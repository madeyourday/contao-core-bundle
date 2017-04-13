<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Menu;

use Contao\CoreBundle\Menu\ArticlePickerProvider;
use Contao\CoreBundle\Menu\PickerMenuProviderInterface;
use Contao\CoreBundle\Tests\TestCase;
use Knp\Menu\MenuFactory;

/**
 * Tests the ArticlePickerProvider class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class ArticlePickerProviderTest extends TestCase
{
    /**
     * @var PickerMenuProviderInterface
     */
    private $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->provider = $this->mockPickerProvider(ArticlePickerProvider::class);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\ArticlePickerProvider', $this->provider);
    }

    /**
     * Tests the createMenu() method.
     */
    public function testCreateMenu()
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('foo');

        $this->provider->createMenu($menu, $factory);

        $this->assertTrue($menu->hasChildren());

        $articlePicker = $menu->getChild('articlePicker');

        $this->assertNotNull($articlePicker);
        $this->assertEquals('articles', $articlePicker->getLinkAttribute('class'));
        $this->assertEquals('contao_backend:do=article', $articlePicker->getUri());
    }

    /**
     * Tests the supports() method.
     */
    public function testSupports()
    {
        $this->assertTrue($this->provider->supports('tl_article'));
        $this->assertFalse($this->provider->supports('tl_page'));
    }

    /**
     * Tests the processSelection() method.
     */
    public function testProcessSelection()
    {
        $this->assertEquals('{{article_url::2}}', $this->provider->processSelection(2));
    }

    /**
     * Tests the canHandle() method.
     */
    public function testCanHandle()
    {
        $this->assertTrue($this->provider->canHandle('{{article_url::2}}'));
        $this->assertFalse($this->provider->canHandle('files/test/foo.jpg'));
    }

    /**
     * Tests the getPickerUrl() method.
     */
    public function testGetPickerUrl()
    {
        $this->assertEquals(
            'contao_backend:value=42:do=article',
            $this->provider->getPickerUrl(['value' => '{{article_url::42}}'])
        );
    }
}
