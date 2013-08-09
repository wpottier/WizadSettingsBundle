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

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class Import
{
    /**
     * @Assert\NotNull()
     * @Assert\File(
     *      maxSize = "5M",
     *      mimeTypes = {"text/yaml", "text/plain"}
     * )
     */
    private $file;

    public function __construct()
    {

    }

    /**
     * @param mixed $file
     *
     * @return Import
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getFileContent()
    {
        if($this->file instanceof UploadedFile) {
            return file_get_contents($this->file->getPathname());
        }

        return null;
    }
}
