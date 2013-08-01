<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;

class Settings implements \ArrayAccess
{
    /**
     * @var ParametersStorageInterface
     */
    private $parametersStorage;

    /**
     * @var ContainerInterface
     */

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    private $schema;

    private $keyDict;

    private $data;

    public function __construct(ParametersStorageInterface $parametersStorage, ContainerInterface $container, $schema)
    {
        $this->parametersStorage = $parametersStorage;
        $this->container         = $container;
        $this->schema            = $schema;
        $this->data = array();

        foreach ($this->schema as $id => $setting) {
            $this->keyDict[$setting['key']] = $id;
        }
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    public function findId($key)
    {
        if (!isset($this->keyDict[$key]))
            return null;

        return $this->keyDict[$key];
    }

    public function formName($key)
    {
        $id = $this->findId($key);

        return sprintf('setting_%s', $id);
    }

    public function __get($name)
    {
        if (strpos($name, 'setting_') === 0) {
            $name = str_replace('setting_', '', $name);

            if (!array_key_exists($name, $this->schema)) {
                trigger_error(sprintf('Property %s does not exist in dynamic settings.', $name));
            }

            return $this->parametersStorage->get($this->schema[$name]['key']);
        } elseif (strpos($name, 'default_setting_') === 0) {
            $name = str_replace('default_setting_', '', $name);

            if (!array_key_exists($name, $this->schema)) {
                trigger_error(sprintf('Property %s does not exist in dynamic settings.', $name));
            }

            return $this->container->getParameter(sprintf('wizad_settings.dynamic.%s', $this->schema[$name]['key']));
        } elseif (strpos($name, 'form_') === 0) {
            $name = str_replace('form_', '', $name);

            return $this->formName($name);
        }

        trigger_error(sprintf('Property %s does not exist in dynamic settings.', $name));
    }

    public function __set($name, $value)
    {
        $name = str_replace('setting_', '', $name);

        if (!array_key_exists($name, $this->schema)) {
            trigger_error(sprintf('Property %s does not exist in dynamic settings.', $name));
        }

        return $this->data[$this->schema[$name]['key']] = $value;
    }

    public function save()
    {
        foreach ($this->data as $key => $value) {
            if (!empty($value)) {
                $this->parametersStorage->set($key, $value);
            } else {
                // Remove value to use default
                $this->parametersStorage->remove($key);
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        if (strpos($offset, 'form_') === 0) {
            $offset = str_replace('form_', '', $offset);

            return array_key_exists($offset, $this->keyDict);
        } elseif (strpos($offset, 'setting_') === 0 || strpos($offset, 'default_setting_') === 0) {
            $offset = str_replace(array('setting_ ', 'default_setting_'), '', $offset);

            return array_key_exists($offset, $this->schema);
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     * </p>
     * @param mixed $value  <p>
     *                      The value to set.
     * </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     * </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}