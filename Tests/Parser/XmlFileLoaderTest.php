<?php

namespace Wizad\SettingsBundle\Tests\Parser;

use Symfony\Component\Config\FileLocator;

use Wizad\SettingsBundle\Parser\XmlFileLoader;
use Wizad\SettingsBundle\Tests\TestCase;

class XmlFileLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new XmlFileLoader(new FileLocator(__DIR__ . '/../Fixtures/Resources/config'));
        $schema = $loader->load('settings.xml');

        $this->assertCount(2, $schema);
    }
}