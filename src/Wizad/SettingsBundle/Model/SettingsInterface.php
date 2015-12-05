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

interface SettingsInterface extends \IteratorAggregate, \ArrayAccess
{
    /**
     * @param array $data
     * @return $this
     */
    public function updateFromArray(array $data);

    /**
     * @return array
     */
    public function toArray();

    /**
     * @param string $key
     * @return bool
     */
    public function isValidKey($key);

    /**
     * @param string $shaId
     * @return bool
     */
    public function isValidFormName($shaId);

    /**
     * @param $shaId
     * @return string
     */
    public function getKeyFromFormName($shaId);

    /**
     * Save current model in the storage
     *
     * @return $this;
     */
    public function save();

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setValue($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function getValue($key);

    /**
     * @param $key
     * @return mixed
     */
    public function getDefaultValue($key);

    /**
     * @param $key
     * @return mixed
     */
    public function getFormName($key);

    /**
     * @param $key
     * @param bool|false $forceRefresh
     * @return mixed
     */
    public function loadValue($key, $forceRefresh = false);
}
