Symfony RequestValidatorBundle
==============================

# Usage

```php
<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Seferov\RequestValidatorBundle\Validator\RequestValidator;
use Seferov\RequestValidatorBundle\Annotation\Validator;

class AcmeController extends Controller
{
    /**
     * @Validator(name="page", default="1", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=1)})
     * @Validator(name="limit", default="25", constraints={@Assert\Type(type="numeric"), @Assert\Range(min=10, max=100)})
     * @Validator(name="order", default="desc", constraints={@Assert\Choice(choices={"asc", "desc"}, message="error.wrong_order_choice")})
     * @Validator(name="name", constraints={@Assert\NotBlank()})
     * @Validator(name="email", required=true, constraints={@Assert\Email()})
     *
     * @param RequestValidator $requestValidator
     */
    public function someAction(RequestValidator $requestValidator)
    {
        // You can get errors if there is any
        /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
        $errors = $requestValidator->getErrors();
        
        // You can get the request value with `get($path)` method
        $email = $requestValidator->get('email');
         
        // ...
    }
}

```

# Installation

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require seferov/request-validator-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Seferov\RequestValidatorBundle\SeferovRequestValidatorBundle(),
        );

        // ...
    }

    // ...
}
```
