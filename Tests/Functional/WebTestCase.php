<?php

namespace Seferov\RequestValidatorBundle\Tests\Functional;

use Seferov\RequestValidatorBundle\Tests\Functional\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * Class WebTestCase.
 */
class WebTestCase extends BaseWebTestCase
{
    protected static function getKernelClass()
    {
        require_once __DIR__.'/app/AppKernel.php';

        return AppKernel::class;
    }
}
