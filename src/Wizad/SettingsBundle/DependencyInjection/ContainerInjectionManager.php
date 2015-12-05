<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\DependencyInjection;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;

class ContainerInjectionManager
{
    private $parametersPrefix;

    private $parametersStorage;

    private $schema;

    public function __construct($parametersPrefix)
    {
        $this->parametersPrefix  = $parametersPrefix;
    }

    /**
     * Inject dynamic parameters in the container builder
     *
     * @param ContainerBuilder $container
     */
    public function inject(ContainerBuilder $container)
    {
        $settings = $container->get('wizad_settings.model.settings');

        foreach($settings as $element) {
            $container->setParameter($this->getParametersName($element->getId()), $settings->loadValue($element->getId()));
        }
    }

    /**
     * Rebuild the container and dump it to the cache to apply change on redis stored parameters
     * @param Kernel $kernel
     */
    public function rebuild(Kernel $kernel)
    {
        $kernelReflectionClass = new \ReflectionClass($kernel);

        $buildContainerReflectionMethod = $kernelReflectionClass->getMethod('buildContainer');
        $buildContainerReflectionMethod->setAccessible(true);

        $dumpContainerReflectionMethod = $kernelReflectionClass->getMethod('dumpContainer');
        $dumpContainerReflectionMethod->setAccessible(true);

        $getContainerClassReflectionMethod = $kernelReflectionClass->getMethod('getContainerClass');
        $getContainerClassReflectionMethod->setAccessible(true);

        $getContainerBaseClassReflectionMethod = $kernelReflectionClass->getMethod('getContainerBaseClass');
        $getContainerBaseClassReflectionMethod->setAccessible(true);

        /** @var ContainerBuilder $newContainer */
        $newContainer = $buildContainerReflectionMethod->invoke($kernel);

        $this->inject($newContainer);

        $newContainer->compile();

        $class = $getContainerClassReflectionMethod->invoke($kernel);
        $cache = new ConfigCache($kernel->getCacheDir() . '/' . $class . '.php', $kernel->isDebug());
        $dumpContainerReflectionMethod->invoke($kernel, $cache, $newContainer, $class, $getContainerBaseClassReflectionMethod->invoke($kernel));
    }

    public function getParametersName($key)
    {
        return sprintf('%s%s', $this->getParametersPrefix(), $key);
    }

    /**
     * Return parameters prefix
     * @return string
     */
    public function getParametersPrefix()
    {
        return $this->parametersPrefix;
    }

    protected function protectParameterValue($value)
    {
        return str_replace('%', '%%', $value);
    }
}