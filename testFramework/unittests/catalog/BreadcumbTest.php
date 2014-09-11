<?php
/**
 * File contains framework test cases
 *
 * @package   tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('HEADER_TITLE_CATALOG')) {
    define('HEADER_TITLE_CATALOG', __DIR__);
}

if (!defined('HTTP_SERVER')) {
    define('HTTP_SERVER', __FILE__);
}

if (!defined('DIR_WS_CATALOG')) {
    define('DIR_WS_CATALOG', __LINE__);
}

/**
 * @see includes/classes/breadcrumb.php
 */
class BreadcrumbTest extends zcCatalogTestCase
{
    /**
     * @var Breadcrumb
     */
    private $breadcrumb;

    /**
     * @var array
     */
    private $links = array(
        'Zen Cart' => 'http://zen-cart.com'
    );

    public function testIsCountable()
    {
        $this->breadcrumb = new Breadcrumb;
        $this->assertInstanceOf('Countable', $this->breadcrumb);
        $this->assertCount(0, $this->breadcrumb);
    }

    public function testPopulationViaInstanciation()
    {
        $this->breadcrumb = new Breadcrumb($this->links);
        $this->assertCount(1, $this->breadcrumb);
    }

    public function testAddThrowsException()
    {
        $this->breadcrumb = new Breadcrumb;
        $this->setExpectedException(
            'InvalidArgumentException',
            'Both title and link must not be empty.'
        );
        $this->breadcrumb->add('');
    }

    public function testTrailGeneratesHtml()
    {
        $expected  = '<nav class="breadcrumb">';
        $expected .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="crumb">';
        $expected .= '<link itemprop="url" href="http://zen-cart.com" class="link" />';
        $expected .= '<span itemprop="title" class="title">Zen Cart</span>';
        $expected .= "</span></nav>\n";

        $this->breadcrumb = new Breadcrumb($this->links);
        $this->assertEquals($expected, $this->breadcrumb->trail());
    }

    public function testCatalogTitleReplaced()
    {
        $this->breadcrumb = new Breadcrumb($this->links);
        $this->breadcrumb->add(HEADER_TITLE_CATALOG, 'foo');
        $this->assertNotContains('foo', $this->breadcrumb->trail());
    }

    public function testStringCastEqualsTrail()
    {
        $this->breadcrumb = new Breadcrumb($this->links);
        $this->assertSame((string) $this->breadcrumb, $this->breadcrumb->trail());
    }
}
