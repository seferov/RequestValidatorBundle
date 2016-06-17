<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var
     */
    private $errors;

    public function __construct(Request $request, ValidatorInterface $validator, array $annotations)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->annotations = $annotations;
    }

    public function getAll()
    {
        return $this->request->query->all();
    }

    public function getErrors()
    {
        $this->errors = [];
        foreach ($this->annotations as $annotation) {
            $violationList = $this->validator->validate($this->request->get($annotation->getName()), $annotation->getConstraints());
            $this->errors[$annotation->getName()] = $violationList;
        }

        return $this->errors;
    }
}
