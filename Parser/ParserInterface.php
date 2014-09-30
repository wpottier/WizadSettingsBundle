<?php

/*
 * This file is part of the WizadSettingBundle package.
 *
 * (c) William Pottier <wpottier@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wizad\SettingsBundle\Parser;

/**
 * Interface ParserInterface
 */
interface ParserInterface
{
    /**
     * @param $file
     * @param null $type
     * @return mixed
     */
    public function load($file);

    /**
     * @param $resource
     * @return mixed
     */
    public function supports($resource);
} 