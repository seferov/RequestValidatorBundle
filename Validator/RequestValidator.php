<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class RequestValidator.
 *
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 */
class RequestValidator
{
    /**
     * @var Validator[]
     */
    private $annotations;

    /**
     * @var ConstraintViolationList
     */
    private $errors;

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
                $this->errors->set($annotation->getName(), $this->validator->validate(null, new NotNull())->get(0));
                continue;
            }

            if (!$this->getParameterBag()->has($annotation->getName()) && $annotation->isOptional()) {
                continue;
            }

            $requestValue = $this->getParameterBag()->get($annotation->getName());

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
                if ($annotation->isRequired()) {
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
