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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Traversable;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;
use Wizad\SettingsBundle\DependencyInjection\ContainerInjectionManager;

class Settings implements ContainerAwareInterface, SettingsInterface
{
    /** @var ParametersStorageInterface */
    private $parametersStorage;

    /** @var ContainerInjectionManager */
    private $containerInjectionManager;

    /** @var ContainerInterface */
    private $container;

    /** @var SettingElement[] */
    private $elements;

    /** @var SettingElement[] */
    private $formSchema;

    /** @var array */
    private $schema;

    public function __construct(ParametersStorageInterface $parametersStorage, ContainerInjectionManager $containerInjectionManager, $schema)
    {
        $this->parametersStorage         = $parametersStorage;
        $this->containerInjectionManager = $containerInjectionManager;
        $this->schema                    = $schema;

        $this->buildFromSchema();
    }

    /**
     * Initialize internal storage
     */
    protected function buildFromSchema()
    {
        $this->elements = [];
        $this->formSchema = [];

        foreach ($this->schema as $id => $setting) {
            $element = new SettingElement(
                $this,
                $id,
                isset($setting['default']) ? $setting['default'] : null
            );

            if (isset($setting['form'])) {
                if (isset($setting['form']['type'])) {
                    $element->setFormType($setting['form']['type']);
                }

                if (isset($setting['form']['options'])) {
                    $element->setFormOptions($setting['form']['options']);
                }
            }

            $this->elements[$element->getId()] = $element;
            $this->formSchema[$element->getFormName()] = $element;
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($key)
    {
        if ($this->isValidKey($key)) {
            return $this->getValue($key);
        }

        if ($this->isValidFormName($key)) {
            return $this->getValue($this->formSchema[$key]->getId());
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @inheritdoc
     */
    public function __set($key, $value)
    {
        if ($this->isValidKey($key)) {
            return $this->setValue($key, $value);
        }

        if ($this->isValidFormName($key)) {
            return $this->setValue($this->formSchema[$key]->getId(), $value);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @return \ArrayIterator|SettingElementInterface[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->isValidKey($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function updateFromArray(array $data)
    {
        foreach ($data as $key => $value) {
            if ($this->isValidKey($key)) {
                $this->setValue($key, $value);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $data = array();

        foreach($this->elements as $element) {
            if (empty($value) || $element->getValue() == $element->getDefaultValue()) {
                continue;
            }

            $data[$element->getId()] = $element->getValue();
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function isValidKey($key)
    {
        return array_key_exists($key, $this->elements);
    }

    /**
     * @inheritdoc
     */
    public function isValidFormName($shaId)
    {
        return array_key_exists($shaId, $this->formSchema);
    }

    /**
     * @inheritdoc
     */
    public function getKeyFromFormName($shaId)
    {
        if (!$this->isValidFormName($shaId)) {
            throw new \InvalidArgumentException();
        }

        return $this->formSchema[$shaId]->getId();
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        foreach ($this->elements as $element) {
            if (!empty($value) && $element->getValue() != $element->getDefaultValue()) {
                $this->parametersStorage->set($element->getId(), $element->getValue());
            } else {
                // Remove value to use default
                $this->parametersStorage->remove($element->getId());
            }
        }

        $this->containerInjectionManager->rebuild($this->container->get('kernel'));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setValue($key, $value)
    {
        if (!$this->isValidKey($key)) {
            throw new \InvalidArgumentException(sprintf('%s is not an existing wizad_settings name', $key));
        }

        return $this->elements[$key]->setValue($value);
    }

    /**
     * @inheritdoc
     */
    public function getValue($key)
    {
        if (!$this->isValidKey($key)) {
            throw new \InvalidArgumentException(sprintf('%s is not an existing wizad_settings name', $key));
        }

        return $this->elements[$key]->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue($key)
    {
        if ($this->isValidKey($key)) {
            return $this->elements[$key]->getDefaultValue();

        }

        if ($this->isValidFormName($key)) {
            return $this->formSchema[$key]->getDefaultValue();
        }

        throw new \InvalidArgumentException(sprintf('%s is not an existing wizad_settings name', $key));
    }

    /**
     * @inheritdoc
     */
    public function getFormName($key)
    {
        if (!$this->isValidKey($key)) {
            throw new \InvalidArgumentException(sprintf('%s is not an existing wizad_settings name', $key));
        }

        return $this->elements[$key]->getFormName();
    }

    /**
     * @inheritdoc
     */
    public function loadValue($key, $forceRefresh = false)
    {
        if (!$this->isValidKey($key)) {
            throw new \InvalidArgumentException(sprintf('%s is not an existing wizad_settings name', $key));
        }

        $parameterName = $this->container->get('wizad_settings.dependency_injection.container_injection_manager')->getParametersName($key);

        if (!$forceRefresh && $this->container->hasParameter($parameterName)) {
            $this->elements[$key]->setValue($this->container->getParameter($parameterName));

            return $this->elements[$key]->getValue();
        }

        if ($this->parametersStorage->has($key)) {
            $this->elements[$key]->setValue($this->parametersStorage->get($key));

            return $this->elements[$key]->getValue();
        }

        $this->elements[$key]->setUseDefaultValue();

        return $this->elements[$key]->getValue();
    }
}