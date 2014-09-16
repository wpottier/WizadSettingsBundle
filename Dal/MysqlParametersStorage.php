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

class MysqlParametersStorage implements ParametersStorageInterface
{
    private $db;

    public function __construct($config)
    {
        $this->db = new \PDO("mysql:host=".$config['host'].";dbname=".$config['dbname'].";charset=utf8", $config['user'], $config['password']);

        $statement = $this->db->prepare("CREATE TABLE IF NOT EXISTS `wizad_settings` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `identifier` varchar(255) NOT NULL DEFAULT '',
                      `value` varchar(255) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `identifier` (`identifier`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $statement->execute();
    }

    public function has($key)
    {
        $statement = $this->db->prepare("SELECT count(id) FROM `wizad_settings` WHERE `identifier`=:key");
        $statement->bindValue(":key", $key);

        $statement->execute();
        $result = $statement->fetch();

        return is_array($result) ? true : false;
    }

    public function get($key)
    {
        $statement = $this->db->prepare("SELECT * FROM `wizad_settings` WHERE `identifier`=:key");
        $statement->bindValue(":key", $key);

        $statement->execute();
        $result = $statement->fetch();

        return is_array($result) ? $result['value'] : false;
    }

    public function set($key, $value)
    {
        $statement = $this->db->prepare("REPLACE INTO `wizad_settings` (identifier, value) VALUES (:identifier, :value)");
        $statement->bindValue(":identifier", $key);
        $statement->bindValue(":value", $value);

        return $statement->execute();
    }

    public function remove($key)
    {
        $statement = $this->db->prepare("DELETE FROM `wizad_settings` WHERE `identifier`=:identifier");
        $statement->bindValue(":identifier", $key);

        return $statement->execute();
    }
}