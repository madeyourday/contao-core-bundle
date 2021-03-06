<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Autoload;

use Contao\CoreBundle\Autoload\BundleAutoloader;

/**
 * Tests the BundleAutoloader class.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class BundleAutoloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $bundleLoader = new BundleAutoloader('rootDir', 'env');

        $this->assertInstanceOf('Contao\CoreBundle\Autoload\BundleAutoloader', $bundleLoader);
    }

    /**
     * Tests the load() method.
     */
    public function testLoad()
    {
        $bundleLoader = new BundleAutoloader(
            __DIR__ . '/../Fixtures/BundleAutoloader/dummyRootDirName',
            'all'
        );

        $this->assertSame(
            [
                'ContaoCoreBundle' => 'Contao\CoreBundle\ContaoCoreBundle',
                'legacy-module'    => null
            ],
            $bundleLoader->load()
        );
    }
}
