<?php

namespace Seferov\RequestValidatorBundle\Tests\Functional\app;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

AnnotationRegistry::registerLoader('class_exists');

/**
 * Class AppKernel.
 */
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Seferov\RequestValidatorBundle\SeferovRequestValidatorBundle(),
            new \Seferov\RequestValidatorBundle\Tests\Functional\TestBundle\TestBundle(),
        ];
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config.yml');
    }
}
