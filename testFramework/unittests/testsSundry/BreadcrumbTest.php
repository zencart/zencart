<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

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
class BreadcrumbTest extends zcTestCase
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

    public function setup()
    {
        parent::setup();
        require_once DIR_FS_CATALOG . 'includes/classes/breadcrumb.php';
    }

    public function testGetLinksReturnsArray()
    {
        $this->breadcrumb = new Breadcrumb;
        $this->assertInternalType('array', $this->breadcrumb->getLinks());
    }

    public function testPopulationViaInstanciation()
    {
        $this->breadcrumb = new Breadcrumb($this->links);
        $this->assertEquals($this->links, $this->breadcrumb->getLinks());
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
        $expected = '<nav class="breadcrumb">';
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
        $this->assertSame((string)$this->breadcrumb, $this->breadcrumb->trail());
    }

    public function testLastReturnsString()
    {
        $this->breadcrumb = new Breadcrumb($this->links);
        $this->setExpectedException(
            'PHPUnit_Framework_Error_Deprecated',
            'Breadcrumb::last is deprecated'
        );
        $this->assertSame('Zen Cart', $this->breadcrumb->last());
    }
}
