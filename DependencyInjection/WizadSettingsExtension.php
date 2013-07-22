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

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Wizad\SettingsBundle\Schema;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WizadSettingsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $schema = $this->loadDynamicParametersSchema($configs, $container);
        $this->injectDynamicParameters($configs, $container, $schema);
    }

    protected function loadDynamicParametersSchema($config , ContainerBuilder $container)
    {

        $bundles = $container->getParameter('kernel.bundles');

        $schema = array();
        foreach($config[0]['bundles'] as $bundle) {

            $reflector = new \ReflectionClass($bundles[$bundle]);

            $loader = new Schema\XmlFileLoader(new FileLocator(dirname($reflector->getFileName()) . '/Resources/config'));
            $schema = array_merge($loader->load('settings.xml'), $schema);
        }

        $container->setParameter('wizad_settings.schema', $schema);

        return $schema;
    }

    protected function injectDynamicParameters($config , ContainerBuilder $container, $schema)
    {
        $prefix  = isset($config[0]['redis']['prefix']) && !empty($config[0]['redis']['prefix']) ? $config[0]['redis']['prefix'] . '.' : '';
        $storage = new RedisParametersStorage(array(
            'dsn'    => $config[0]['redis']['dsn'],
            'prefix' => $prefix
        ));

        foreach ($schema as $parameter) {

            $value = $parameter['default'];

            if($storage->has($parameter['key'])) {
                $value = $storage->get($parameter['key']);
            }

            $container->setParameter('wizad_settings.dynamic.'.$parameter['key'], $value);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        return new Configuration(array_keys($bundles));
    }
}
