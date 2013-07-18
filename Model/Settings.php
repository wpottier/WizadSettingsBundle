<?php

namespace Wizad\SettingsBundle\Model;


use Symfony\Component\DependencyInjection\ContainerInterface;

class Settings
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    private $schema;

    public function __construct(ContainerInterface $container, $schema)
    {
        $this->container = $container;
        $this->schema = $schema;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    public function __get($name)
    {
        $name = str_replace('setting_', '', $name);

        if(!array_key_exists($name, $this->schema)) {
            trigger_error(sprintf('Property %s does not exist in dynamic settings.', $name));
        }

        return $this->container->getParameter(sprintf('wizad_settings.dynamic.%s', $this->schema[$name]['key']));
    }

    public function __set($name, $value)
    {

    }

}