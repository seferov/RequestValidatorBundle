<?php

namespace Seferov\RequestValidatorBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Seferov\RequestValidatorBundle\Annotation\Validator;
use Seferov\RequestValidatorBundle\Validator\RequestValidator;
use Seferov\RequestValidatorBundle\Validator\RequestValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorAnnotationListener
{
    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader, ValidatorInterface $validator)
    {
        $this->reader = $reader;
        $this->validator = $validator;
    }

    public function onKernelController(ControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $annotations = $this->reader->getMethodAnnotations($method);

        // Filter out Validator annotations
        $annotations = array_filter($annotations, function ($annotation) {
            return (bool) ($annotation instanceof Validator);
        });

        $validators = [];
        array_walk($annotations, function ($value) use (&$validators) {
            /* @var Validator $value */
            $validators[$value->getName()] = $value;
        });

        $request->attributes->set('requestValidator', $this->createRequestValidator($request, $validators));
    }

    /**
     * @param Request $request
     * @param array   $validators
     *
     * @return RequestValidatorInterface
     */
    protected function createRequestValidator(Request $request, array $validators)
    {
        return new RequestValidator($request, $this->validator, $validators);
    }
}
