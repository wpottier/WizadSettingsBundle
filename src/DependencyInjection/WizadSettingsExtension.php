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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;
use Wizad\SettingsBundle\Parser;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WizadSettingsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('wizad_settings');

        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        // Initialize Settings service
        $this->loadStorageEngine($config, $container);

        // Load settings schema
        $this->loadDynamicParametersSchema($config, $container);

        // Inject parameters
        $container->get('wizad_settings.dependency_injection.container_injection_manager')->inject($container);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     *
     * @return ParametersStorageInterface
     * @throws \Exception
     */
    protected function loadStorageEngine($config, ContainerBuilder $container)
    {
        $parametersStorageServiceDef = $container->getDefinition('wizad_settings.dal.parameters_storage');

        if (isset($config['redis'])) {
            $this->prepareRedisStorageEngine($config['redis'], $container, $parametersStorageServiceDef);
        } elseif (isset($config['mysql'])) {
            $this->prepareMysqlStorageEngine($config['mysql'], $container, $parametersStorageServiceDef);
        } else {
            throw new \Exception('There\'s no valid storage configured for the WizadSettingBundle. Please configure redis or mysql storage.');
        }

        $parametersStorageServiceDef->addArgument($container->getParameter('wizad_settings.config.storage'));
        return $container->get('wizad_settings.dal.parameters_storage');
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     * @param Definition $parametersStorageServiceDef
     */
    protected function prepareRedisStorageEngine($config, ContainerBuilder $container, Definition $parametersStorageServiceDef)
    {
        $prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] . '.' : '';
        $container->setParameter('wizad_settings.config.storage', array(
            'dsn' => $config['dsn'],
            'prefix' => $prefix
        ));
        $parametersStorageServiceDef->setClass($container->getParameter('wizad_settings.dal.redis.class'));
    }

    /**
     * @param $config
     * @param ContainerBuilder $container
     * @param Definition $parametersStorageServiceDef
     */
    protected function prepareMysqlStorageEngine($config, ContainerBuilder $container, Definition $parametersStorageServiceDef)
    {
        $container->setParameter('wizad_settings.config.storage', array(
            'host' => $config['host'],
            'user' => $config['user'],
            'password' => $config['password'],
            'dbname' => $config['dbname']
        ));
        $parametersStorageServiceDef->setClass($container->getParameter('wizad_settings.dal.mysql.class'));
    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function loadDynamicParametersSchema($config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $kernelRootDir = $container->getParameter('kernel.root_dir');

        $schema = array();
        foreach ($config['bundles'] as $bundle) {
            $reflector = new \ReflectionClass($bundles[$bundle]);

            $loader = new $config['config_file_parser'](new FileLocator([
                dirname($reflector->getFileName()) . '/Resources/config',
                $kernelRootDir . '/Resources/' . $bundle . '/config',
            ]));
            $schema = array_merge($loader->load('wizad_settings.xml'), $schema);
        }

        // Search if file exist in main
        if (file_exists($kernelRootDir . '/config/wizad_settings.xml')) {
            $loader = new $config['config_file_parser'](new FileLocator([
                $kernelRootDir . '/config',
            ]));
            $schema = array_merge($loader->load('wizad_settings.xml'), $schema);
        }

        // Store schema
        $container->setParameter('wizad_settings.schema', $schema);

        return $schema;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration(array_keys($container->getParameter('kernel.bundles')));
    }
}
