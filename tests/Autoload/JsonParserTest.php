<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Autoload;

use Contao\CoreBundle\Autoload\JsonParser;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Tests the JsonParser class.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class JsonParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $parser = new JsonParser();

        $this->assertInstanceOf('Contao\CoreBundle\Autoload\JsonParser', $parser);
        $this->assertInstanceOf('Contao\CoreBundle\Autoload\ParserInterface', $parser);
    }

    /**
     * Tests parsing a regular autoload.json file.
     */
    public function testRegularFile()
    {
        $parser = new JsonParser();

        $file = new SplFileInfo(
            __DIR__ . '/../Fixtures/JsonParser/regular/autoload.json',
            'relativePath',
            'relativePathName'
        );

        $this->assertSame(
            [
                'bundles' => [
                    'Contao\CoreBundle\ContaoCoreBundle' => [
                        'class'         => 'Contao\CoreBundle\ContaoCoreBundle',
                        'name'          => 'ContaoCoreBundle',
                        'replace'       => [],
                        'environments'  => ['all'],
                        'load-after'    => []
                    ]
                ]
            ],
            $parser->parse($file)
        );
    }

    /**
     * Tests parsing an autoload.json file without keys.
     */
    public function testFileWithoutKeys()
    {
        $parser = new JsonParser();

        $file = new SplFileInfo(
            __DIR__ . '/../Fixtures/JsonParser/no-keys-defined/autoload.json',
            'relativePath',
            'relativePathName'
        );

        $this->assertSame(
            [
                'bundles' => [
                    'Contao\CoreBundle\ContaoCoreBundle' => [
                        'class'         => 'Contao\CoreBundle\ContaoCoreBundle',
                        'name'          => 'ContaoCoreBundle',
                        'replace'       => [],
                        'environments'  => ['all'],
                        'load-after'    => []
                    ]
                ]
            ],
            $parser->parse($file));
    }

    /**
     * Tests parsing an invalid .json file.
     *
     * @expectedException \RuntimeException
     */
    public function testInvalidFile()
    {
        $parser = new JsonParser();

        $file = new SplFileInfo(
            __DIR__ . '/../Fixtures/JsonParser/invalid/autoload.json',
            'relativePath',
            'relativePathName'
        );

        $parser->parse($file);
    }

    /**
     * Tests parsing a .json file without bundles key.
     *
     * @expectedException \RuntimeException
     */
    public function testFileWithoutBundlesKeys()
    {
        $parser = new JsonParser();

        $file = new SplFileInfo(
            __DIR__ . '/../Fixtures/JsonParser/no-bundles-key/autoload.json',
            'relativePath',
            'relativePathName'
        );

        $parser->parse($file);
    }

    /**
     * Tests parsing a non-existent file.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testNonExistentFile()
    {
        $parser = new JsonParser();
        $file   = new SplFileInfo('iDoNotExist', 'relativePath', 'relativePathName');

        $parser->parse($file);
    }
}
