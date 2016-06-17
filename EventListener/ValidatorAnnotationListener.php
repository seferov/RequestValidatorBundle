<?php

namespace Seferov\RequestValidatorBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Seferov\RequestValidatorBundle\Annotation\Validator;
use Seferov\RequestValidatorBundle\Validator\RequestValidator;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidatorAnnotationListener.
 *
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 */
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

    public function onKernelController(FilterControllerEvent $event)
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

        if (count($annotations)) {
            $request->attributes->set('requestValidator', new RequestValidator($request, $this->validator, $annotations));
        }
    }
}
