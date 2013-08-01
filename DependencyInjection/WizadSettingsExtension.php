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
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;
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
        $processor     = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        // Initialize Settings service
        $storageEngine = $this->loadStorageEngine($config, $container);

        // Load settings schema
        $schema = $this->loadDynamicParametersSchema($config, $container);

        // Inject parameters
        $this->injectDynamicParameters($config, $container, $schema, $storageEngine);
    }

    protected function loadStorageEngine($config, ContainerBuilder $container)
    {
        if(isset($config['redis'])) {

            $prefix  = isset($config['redis']['prefix']) && !empty($config['redis']['prefix']) ? $config['redis']['prefix'] . '.' : '';
            $container->setParameter('wizad_settings.config.storage', array(
                'dsn'    => $config['redis']['dsn'],
                'prefix' => $prefix
            ));

            $parametersStorageServiceDef = $container->getDefinition('wizad_settings.dal.parameters_storage');
            $parametersStorageServiceDef
                ->setClass($container->getParameter('wizad_settings.dal.redis.class'))
                ->addArgument($container->getParameter('wizad_settings.config.storage'))
            ;

            return $container->get('wizad_settings.dal.parameters_storage');
        }

        throw new \Exception('Unsupport storage');
    }

    protected function loadDynamicParametersSchema($config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $schema = array();
        foreach ($config['bundles'] as $bundle) {

            $reflector = new \ReflectionClass($bundles[$bundle]);

            $loader = new Schema\XmlFileLoader(new FileLocator(dirname($reflector->getFileName()) . '/Resources/config'));
            $schema = array_merge($loader->load('settings.xml'), $schema);
        }

        $container->setParameter('wizad_settings.schema', $schema);

        return $schema;
    }

    protected function injectDynamicParameters($config, ContainerBuilder $container, $schema, ParametersStorageInterface $parametersStorage)
    {
        foreach ($schema as $parameter) {

            $value = $parameter['default'];

            if($parametersStorage->has($parameter['key'])) {
                $value = $parametersStorage->get($parameter['key']);
            }

            $container->setParameter('wizad_settings.dynamic.' . $parameter['key'], $value);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        return new Configuration(array_keys($bundles));
    }
}
