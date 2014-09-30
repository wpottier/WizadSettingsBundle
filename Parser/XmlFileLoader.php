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

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XmlFileLoader implements ParserInterface
{
    const NAMESPACE_URI = 'http://william-pottier.fr/schema/settings';
    const SCHEME_PATH   = '/../Resources/schema/settings-1.0.xsd';

    /**
     * @var \Symfony\Component\Config\FileLocatorInterface
     */
    private $locator;

    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }


    /**
     * Loads an XML file.
     *
     * @param mixed $file The resource
     * @return array
     */
    public function load($file)
    {
        $schema = array();

        $path     = $this->locator->locate($file);
        $xml      = $this->parseFile($path);
        $document = $xml->documentElement;

        /** @var \DOMElement $setting */
        $prefix = $document->hasAttribute('prefix') ? $document->getAttribute('prefix') . '.' : '';

        foreach ($document->getElementsByTagName('parameter') as $node) {
            $this->parseParameter($node, $schema, $prefix);
        }

        return $schema;
    }

    protected function parseParameter(\DOMElement $node, &$schema, $prefix)
    {
        $parameter = array(
            'key' => $prefix . $node->getAttribute('key'),
        );

        foreach ($node->childNodes as $n) {
            if (!$n instanceof \DOMElement) {
                continue;
            }

            switch ($n->localName) {
                case 'form':
                    $parameter['form'] = array(
                        'type'    => $n->hasAttribute('type') ? $n->getAttribute('type') : 'text',
                        'options' => array()
                    );
                    break;
                default:
                    $parameter[$n->localName] = $n->textContent;
                    break;
            }
        }

        if (!isset($parameter['form'])) {
            $parameter['form'] = array();
        }

        if (!isset($parameter['form']['type'])) {
            $parameter['form']['type'] = 'text';
        }

        $schema[sha1($parameter['key'])] = $parameter;
    }


    /**
     * Parses a XML file.
     *
     * @param string $file Path to a file
     *
     * @return \DOMDocument
     *
     * @throws \InvalidArgumentException When loading of XML file returns error
     */
    protected function parseFile($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__ . static::SCHEME_PATH);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return $dom;
    }

}