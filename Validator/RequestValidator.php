<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
                $this->errors->set($annotation->getName(), $this->validator->validate(null, new Assert\NotNull())->get(0));
                continue;
            }

            if (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isOptional()) {
                continue;
            }

            $requestValue = $this->getParameterBag()->get($annotation->getName());

            // Fix for Assert\All constraint
            foreach ($annotation->getConstraints() as $constraint) {
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
     * @param $path
     *
     * @return mixed
     */
    public function get($path)
    {
        if (!$this->getParameterBag()->has($path)) {
            return $this->getAnnotation($path)->getDefault();
        }

        return $this->getParameterBag()->get($path);
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

            $requestValue = $this->getParameterBag()->get($annotation->getName());

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

        return;
    }
}
