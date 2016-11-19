<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RequestValidator.
 *
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 */
class RequestValidator implements RequestValidatorInterface
{
    /**
     * @var Validator[]
     */
    private $annotations;

    /**
     * @var ConstraintViolationList
     */
    private $errors;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request, ValidatorInterface $validator, array $annotations)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->annotations = $annotations;
    }

    /**
     * @return ConstraintViolationList
     */
    public function getErrors()
    {
        $this->errors = new ConstraintViolationList();
        foreach ($this->annotations as $annotation) {
            if (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isRequired()) {
                $violation = $this->validator->validate(null, new Assert\NotNull())->get(0);
                $this->errors->set($annotation->getName(), $violation);
                continue;
            }

            if (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isOptional()) {
                continue;
            }

            $requestValue = $this->getParameterBag()->get($annotation->getName());

            // Adjust Symfony constraints to request validator
            foreach ($annotation->getConstraints() as $constraint) {
                // Fix for All constraint
                if ($constraint instanceof Assert\All) {
                    if ($requestValue === null) {
                        $error = $this->validator->validate(null, new Assert\NotNull())->get(0);
                    } elseif (!is_array($requestValue)) {
                        $error = $this->validator->validate($requestValue, new Assert\Type(['type' => 'array']))->get(0);
                    } else {
                        continue;
                    }

                    $this->errors->set($annotation->getName(), $error);

                    continue 2;
                }

                if ($constraint instanceof Assert\Type && 'boolean' == $constraint->type && $this->isBoolean($requestValue)) {
                    $requestValue = filter_var($requestValue, FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Validate the value with all the constraints defined
            $violationList = $this->validator->validate($requestValue, $annotation->getConstraints());
            foreach ($violationList as $violation) {
                $this->errors->set($annotation->getName(), $violation);
            }
        }

        return $this->errors;
    }

    /**
     * Gets value. If request does not have value, returns default.
     *
     * @param $path
     *
     * @return mixed
     */
    public function get($path)
    {
        $annotation = $this->getAnnotation($path);

        if (!$annotation) {
            return;
        }

        if (!$this->getParameterBag()->has($path)) {
            return $annotation->getDefault();
        }

        $requestValue = $this->getParameterBag()->get($path);

        foreach ($annotation->getConstraints() as $constraint) {
            if ($constraint instanceof Assert\Type && 'boolean' == $constraint->type && $this->isBoolean($requestValue)) {
                return filter_var($requestValue, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $requestValue;
    }

    /**
     * Overwrites erroneous values with default one.
     *
     * @return array
     */
    public function getAll()
    {
        $all = [];

        foreach ($this->annotations as $annotation) {
            if (!$this->getParameterBag()->has($annotation->getName())) {
                if ($annotation->isRequired() || $annotation->getDefault() || is_array($annotation->getDefault())) {
                    $all[$annotation->getName()] = $annotation->getDefault();
                }
                continue;
            }

            $requestValue = $this->get($annotation->getName());

            $violationList = $this->validator->validate($requestValue, $annotation->getConstraints());
            $all[$annotation->getName()] = count($violationList)
                ? $annotation->getDefault()
                : $requestValue;
        }

        return $all;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    private function getParameterBag()
    {
        if ('GET' == $this->request->getMethod()) {
            return $this->request->query;
        }

        return $this->request->request;
    }

    /**
     * @param $path
     *
     * @return Validator|null
     */
    private function getAnnotation($path)
    {
        if (array_key_exists($path, $this->annotations)) {
            return $this->annotations[$path];
        }
    }

    /**
     * On boolean type request values with 0 and 1 should be considered as false and true respectively.
     *
     * @param $s
     *
     * @return bool
     */
    private function isBoolean($s)
    {
        if (filter_var($s, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            return false;
        }

        return true;
    }
}
