<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\DependencyInjection;

use Contao\CoreBundle\DependencyInjection\ContaoCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the ContaoCoreExtension class.
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoCoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContaoCoreExtension
     */
    protected $extension;

    /**
     * Creates the core extension object.
     */
    protected function setUp()
    {
        $this->extension = new ContaoCoreExtension();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\ContaoCoreExtension', $this->extension);
    }

    /**
     * Tests adding the bundle services to the container.
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();

        $this->extension->load([], $container);

        $this->assertTrue($container->has('contao.listener.output_from_cache'));
        $this->assertTrue($container->has('contao.listener.add_to_search_index'));
    }
}
