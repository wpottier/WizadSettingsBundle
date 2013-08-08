<?php

namespace Wizad\SettingsBundle\Tests\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Wizad\SettingsBundle\Parser\XmlFileLoader;
use Wizad\SettingsBundle\Tests\TestCase;

class ContainerInjectionManagerTest extends TestCase
{
    /**
     * @dataProvider dataTest
     */
    public function testLoad($schema)
    {
        $this->assertTrue(true);
    }

    private function dataTest()
    {
        return array(array(
            '20a712ac73aa0eca30ad8d6bcf6a37d3c20b1b07' => array(
                'key'     => 'my_site.email.sender_name',
                'name'    => 'Email sender name',
                'default' => 'Me',
                'form'    => array(
                    'type' => 'text'
                )
            ),
            'd3287f3eb476a2c66ee5ee8b30beea83ddbfcebc' => array(
                'key'     => 'my_site.email.sender_email',
                'name'    => 'Email sender address',
                'default' => 'me@my-site.com',
                'form'    => array(
                    'type' => 'text'
                )
            )
        ));
    }
}