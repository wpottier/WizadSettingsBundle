<?php

namespace Wizad\SettingsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    private $bundles;

    /**
     * Constructor
     *
     * @param array $bundles An array of bundle names
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wizad_settings');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('memcache')
                ->info('memcache access')
                    ->children()
                        ->scalarNode('ip')->isRequired()->end()
                        ->scalarNode('port')->defaultValue(1211)->end()
                        ->scalarNode('prefix')->defaultValue("symfony.parameters.dynamic")->end()
                    ->end()
                ->end()
                ->arrayNode('bundles')
                    ->defaultValue($this->bundles)
                    ->prototype('scalar')
                    ->validate()
                        ->ifNotInArray($this->bundles)
                        ->thenInvalid('%s is not a valid bundle.')
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
