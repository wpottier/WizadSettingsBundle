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

class SettingElement implements SettingElementInterface
{
    /** @var Settings */
    private $manager;

    /** @var string */
    private $id;

    /** @var bool */
    private $loaded;

    /** @var mixed */
    private $value;

    /** @var mixed */
    private $defaultValue;

    /** @var array */
    private $form;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function isValueLoaded()
    {
        return $this->loaded;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->loaded = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function setUseDefaultValue()
    {
        $this->setValue($this->getDefaultValue());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFormType($formType)
    {
        $this->form['type'] = $formType;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormType()
    {
        return $this->form['type'];
    }

    /**
     * @inheritdoc
     */
    public function setFormOptions(array $options)
    {
        $this->form['options'] = $options;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormOptions()
    {
        return $this->form['options'];
    }

    /**
     * @inheritdoc
     */
    public function getFormName()
    {
        return $this->form['name'];
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if (!$this->loaded) {
            $this->manager->loadValue($this->id);
        }

        return $this->value;
    }

    public function __construct(Settings $manager, $id, $defaultValue)
    {
        $this->manager = $manager;
        $this->id = $id;
        $this->defaultValue = $defaultValue;
        $this->loaded = false;
        $this->form = [
            'name' => sha1($this->id),
            'type' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'options' => [],
        ];
    }
}
