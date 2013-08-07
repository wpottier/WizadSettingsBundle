<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Tests\Functional;

// get the autoload file
require_once __DIR__.'/../../../vendor/autoload.php';

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * App Test Kernel for functional tests.
 */
class AppKernel extends Kernel
{
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Wizad\SettingsBundle\WizadSettingsBundle(),
            new \Wizad\SettingsBundle\Tests\Fixtures\WizadSettingsTestBundle(),
        );
    }

    public function init()
    {
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/wizad-settings/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/wizad-settings/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

    public function serialize()
    {
        return serialize(array($this->getEnvironment(), $this->isDebug()));
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }
}