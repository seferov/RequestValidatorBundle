<?php

namespace Seferov\RequestValidatorBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use Symfony\Component\Validator\Constraint;

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
     * @var array|Constraint[]
     */
    private $constraints = [];

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var mixed
     */
    private $default = null;

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
     * @return array|Constraint[]
     */
    public function getConstraints()
    {
        return array_values($this->constraints);
    }

    /**
     * @param array $constraints
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }

    /**
     * @param $key
     */
    public function removeConstraint($key)
    {
        unset($this->constraints[$key]);
    }

    /**
     * @param Constraint $constraint
     */
    public function addConstraint(Constraint $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return !$this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }
}
