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
use Symfony\Component\HttpKernel\Kernel;
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

        $processor     = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $processor->processConfiguration($configuration, $configs);

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

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function loadDynamicParametersSchema($config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $schema = array();
        foreach ($config['bundles'] as $bundle) {

            $reflector = new \ReflectionClass($bundles[$bundle]);

            $loader = new Parser\XmlFileLoader(new FileLocator(dirname($reflector->getFileName()) . '/Resources/config'));
            $schema = array_merge($loader->load('settings.xml'), $schema);
        }

        $container->setParameter('wizad_settings.schema', $schema);

        return $schema;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        return new Configuration(array_keys($bundles));
    }
}
