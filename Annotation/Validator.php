<?php

namespace Seferov\RequestValidatorBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 *
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 */
class Validator extends ConfigurationAnnotation
{
    /**
     * @var
     */
    private $name;

    /**
     * @var array
     */
    private $constraints = [];

    /**
     * @var bool
     */
    private $optional;

    public function getAliasName()
    {
        return 'request_validator';
    }

    public function allowArray()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @param bool $optional
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;
    }

    /**
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param array $constraints
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }
}
