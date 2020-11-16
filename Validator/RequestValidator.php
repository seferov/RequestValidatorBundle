<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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

        $allFields = $this->getAll(false, false);

        foreach ($this->annotations as $annotation) {
            $requestValue = $this->getParameterBag()->get($annotation->getName());

            // Add NotNull for required empty params
            if (!$this->getParameterBag()->get($annotation->getName()) && $annotation->isRequired()) {
                $annotation->addConstraint(new Assert\NotNull());
            }

            // Adjust Symfony constraints to request validator
            foreach ($annotation->getConstraints() as $key => $constraint) {
                // Conditional constraints
                if (isset($constraint->payload['when'])) {
                    $language = new ExpressionLanguage();
                    $condition = $language->evaluate($constraint->payload['when'], $allFields);

                    if (!$condition) {
                        $annotation->removeConstraint($key);
                        continue;
                    }
                }
                // Skip not required and empty params
                elseif (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isOptional()) {
                    $annotation->removeConstraint($key);
                    continue;
                }

                // Fix for All constraint
                if ($constraint instanceof Assert\All) {
                    if ($requestValue === null) {
                        $error = $this->validator->validate([
                            $annotation->getName() => $requestValue,
                        ], new Assert\NotNull())->get(0);
                    } elseif (!is_array($requestValue)) {
                        $error = $this->validator->validate([
                            $annotation->getName() => $requestValue,
                        ], new Assert\Type(['type' => 'array']))->get(0);
                    } else {
                        continue;
                    }

                    $this->errors->add($error);

                    continue 2;
                }

                // Fix for Type=boolean
                if ($constraint instanceof Assert\Type && 'boolean' == $constraint->type && $this->isBoolean($requestValue)) {
                    $requestValue = filter_var($requestValue, FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Validate the value with all the constraints defined
            $violationList = $this->validator->validate([
                $annotation->getName() => $requestValue,
            ], $annotation->getConstraints());
            $this->errors->addAll($violationList);
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
     * @param bool $validate    Overwrites erroneous values with default one
     * @param bool $skipMissing
     *
     * @return array
     */
    public function getAll($validate = true, $skipMissing = true)
    {
        $all = [];

        foreach ($this->annotations as $annotation) {
            if (!$this->getParameterBag()->has($annotation->getName())) {
                if ($annotation->isRequired() || $annotation->getDefault() || is_array($annotation->getDefault())) {
                    $all[$annotation->getName()] = $annotation->getDefault();
                }

                if ($skipMissing) {
                    continue;
                }
            }

            $requestValue = $this->get($annotation->getName());

            if ($validate) {
                $violationList = $this->validator->validate($requestValue, $annotation->getConstraints());
                $all[$annotation->getName()] = count($violationList)
                    ? $annotation->getDefault()
                    : $requestValue;
            } else {
                $all[$annotation->getName()] = $requestValue;
            }
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
