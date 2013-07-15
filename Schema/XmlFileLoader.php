<?php

namespace Wizad\SettingsBundle\Schema;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class XmlFileLoader
{
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
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }


    /**
     * Loads an XML file.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     */
    public function load($file, $type = null)
    {
        $schema = array();

        $path = $this->locator->locate($file);
        $xml = $this->parseFile($path);
        $document = $xml->documentElement;

        /** @var \DOMElement $setting */
        $prefix = $document->hasAttribute('prefix') ? $document->getAttribute('prefix').'.' : '';

        foreach ($document->getElementsByTagName('parameter') as $node) {
            $this->parseParameter($node, $schema, $prefix);
        }

        return $schema;
    }

    protected function parseParameter(\DOMElement $node, &$schema, $prefix)
    {
        $parameter = array(
            'key' => $prefix.$node->getAttribute('key'),
        );

        foreach ($node->childNodes as $n) {
            if (!$n instanceof \DOMElement) {
                continue;
            }

            $parameter[$n->localName] = $n->textContent;
        }

        $schema[$parameter['key']] = $parameter;
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
            $dom = XmlUtils::loadFile($file);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return $dom;
    }

}