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

interface SettingElementInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return mixed
     */
    public function isValueLoaded();

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * @return string|\Symfony\Component\Form\AbstractType
     */
    public function getFormType();

    /**
     * @return string
     */
    public function getFormName();

    /**
     * @return array|null
     */
    public function getFormOptions();
}