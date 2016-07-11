<?php

namespace Seferov\RequestValidatorBundle\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Interface RequestValidatorInterface
 * @package Seferov\RequestValidatorBundle\Validator
 */
interface RequestValidatorInterface
{
    /**
     * @return ConstraintViolationList
     */
    public function getErrors();

    /**
     * @param string $path
     * @return mixed
     */
    public function get($path);

    /**
     * @return array
     */
    public function getAll();

    /**
     * @return Request
     */
    public function getRequest();
}
