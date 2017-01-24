<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Dal;

use Predis\Client;

class RedisParametersStorage implements ParametersStorageInterface
{
    private $predis;

    public function __construct($config)
    {
        $this->predis = new Client($config['dsn'], array(
            'prefix' => $config['prefix']
        ));
    }

    public function has($key)
    {
        if(!$this->predis->isConnected())
            $this->predis->connect();

        return $this->predis->exists($key);
    }

    public function get($key)
    {
        if(!$this->predis->isConnected())
            $this->predis->connect();

        return $this->predis->get($key);
    }

    public function set($key, $value)
    {
        if(!$this->predis->isConnected())
            $this->predis->connect();

        return $this->predis->set($key, $value);
    }

    public function remove($key)
    {
        if(!$this->predis->isConnected())
            $this->predis->connect();

        return $this->predis->del($key);
    }
}