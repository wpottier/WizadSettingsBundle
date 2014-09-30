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
                ->scalarNode('config_file_parser')->defaultValue('\Wizad\SettingsBundle\Parser\XmlFileLoader')->end()
                ->arrayNode('redis')
                ->info('redis access')
                    ->children()
                        ->scalarNode('dsn')->defaultValue('tcp://127.0.0.1:6379')->isRequired()->end()
                        ->scalarNode('prefix')->defaultValue("symfony.parameters.dynamic")->end()
                    ->end()
                ->end()
                ->arrayNode('mysql')
                ->info('mysql access')
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->isRequired()->end()
                        ->scalarNode('user')->defaultValue("")->isRequired()->end()
                        ->scalarNode('password')->defaultValue("")->isRequired()->end()
                        ->scalarNode('dbname')->defaultValue("settings")->isRequired()->end()
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
