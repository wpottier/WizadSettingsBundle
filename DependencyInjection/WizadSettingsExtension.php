<?php

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

        return $schema;
    }

    protected function injectDynamicParameters($config , ContainerBuilder $container, $schema)
    {
        $m = new \Memcached();
        $m->addServer('localhost', 11211);
        $prefix = isset($config[0]['memcache']['prefix']) && !empty($config[0]['memcache']['prefix']) ? $config[0]['memcache']['prefix'].'.' : '';

        foreach($schema as $key => $parameter) {

            $fqk = $prefix.$key;
            $value = $m->get($fqk);
            if($value === false) {
                $value = $parameter['default'];
            }

            $container->setParameter('wizad_settings.dynamic.'.$key, $value);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        return new Configuration(array_keys($bundles));
    }
}
