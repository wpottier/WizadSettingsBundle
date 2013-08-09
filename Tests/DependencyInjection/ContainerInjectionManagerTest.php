<?php

namespace Wizad\SettingsBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Wizad\SettingsBundle\DependencyInjection\ContainerInjectionManager;
use Wizad\SettingsBundle\Parser\XmlFileLoader;
use Wizad\SettingsBundle\Tests\TestCase;

class ContainerInjectionManagerTest extends TestCase
{
    /**
     * @dataProvider dataTest
     */
    public function testLoad($schema)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array());

        $container
            ->expects($this->exactly(count($schema)))
            ->method('setParameter')
            ->with(
                $this->logicalOr($this->identicalTo('my_site.email.sender_name'), $this->identicalTo('my_site.email.sender_email')),
                $this->logicalOr($this->identicalTo('Me'), $this->identicalTo('me@my-site.com'))
            )
            ;

        $injecter = new ContainerInjectionManager($this->getEmptyDalMocked(), $schema, '');
        $injecter->inject($container);
    }

    public function dataTest()
    {
        return array(
            array(
                array(
                    '20a712ac73aa0eca30ad8d6bcf6a37d3c20b1b07'  => array(
                        'key'     => 'my_site.email.sender_name',
                        'name'    => 'Email sender name',
                        'default' => 'Me',
                        'form'    => array(
                            'type' => 'text'
                        )
                    ),
                    'd3287f3eb476<a2c66ee5ee8b30beea83ddbfcebc' => array(
                        'key'     => 'my_site.email.sender_email',
                        'name'    => 'Email sender address',
                        'default' => 'me@my-site.com',
                        'form'    => array(
                            'type' => 'text'
                        )
                    )
                )
            )
        );
    }

    /**
     * @return \Wizad\SettingsBundle\Dal\ParametersStorageInterface
     */
    protected function getEmptyDalMocked()
    {
        $mock = $this->getMock('Wizad\SettingsBundle\Dal\ParametersStorageInterface');
        $mock
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue(false));

        return $mock;
    }

}