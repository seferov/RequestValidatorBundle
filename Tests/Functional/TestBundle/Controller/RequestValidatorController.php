<?php

namespace Seferov\RequestValidatorBundle\Tests\Functional\TestBundle\Controller;

use Seferov\RequestValidatorBundle\Annotation\Validator;
use Seferov\RequestValidatorBundle\Validator\RequestValidator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RequestValidatorController.
 */
class RequestValidatorController extends Controller
{
    /**
     * @Validator(name="name", constraints={@Assert\Length(min=3)})
     * @Validator(name="email", constraints={@Assert\Email()})
     * @Validator(name="order", constraints={@Assert\Choice(choices={"asc", "desc"})})
     * @Validator(name="page", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=1)})
     *
     * @param RequestValidator $requestValidator
     *
     * @return Response
     */
    public function violationsAction(RequestValidator $requestValidator)
    {
        $errors = $requestValidator->getErrors();

        $response = [];
        foreach ($errors as $error) {
            $response[$error->getRoot()] = $error->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * @Validator(name="page", default=1, constraints={@Assert\Type(type="numeric"), @Assert\Range(min=1)})
     * @Validator(name="order", default="asc", constraints={@Assert\Choice(choices={"asc", "desc"})})
     *
     * @param RequestValidator $requestValidator
     *
     * @return JsonResponse
     */
    public function defaultAction(RequestValidator $requestValidator)
    {
        return new JsonResponse($requestValidator->getAll());
    }

    /**
     * Not required fields.
     *
     * @Validator(name="page", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=1)})
     * @Validator(name="order", constraints={@Assert\Choice(choices={"asc", "desc"})})
     * @Validator(name="emails", constraints={@Assert\All(@Assert\Email)})
     *
     * @param RequestValidator $requestValidator
     *
     * @return JsonResponse
     */
    public function notRequiredAction(RequestValidator $requestValidator)
    {
        $errors = $requestValidator->getErrors();
        $response = [];
        foreach ($errors as $error) {
            $response[$error->getRoot()] = $error->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * NotBlank constraint.
     *
     * @Validator(name="name", constraints={@Assert\NotBlank})
     *
     * @param RequestValidator $requestValidator
     *
     * @return JsonResponse
     */
    public function notBlankAction(RequestValidator $requestValidator)
    {
        $errors = $requestValidator->getErrors();
        $response = [];
        foreach ($errors as $error) {
            $response[$error->getRoot()] = $error->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * no validator annotation!
     *
     * @param RequestValidator $requestValidator
     *
     * @return JsonResponse
     */
    public function noValidatorAction(RequestValidator $requestValidator)
    {
        return new JsonResponse($requestValidator->getAll());
    }
}
