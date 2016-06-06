<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * @see /includes/functions/functions_lookups.php
 */
class AttributeLookupsTest extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';
    }

    public function testZenHasProductAttributesDownloadsStatusWhenDownloadEnabledIsFalse()
    {
        define('DOWNLOAD_ENABLED', 'false');

        global $db;
        $db = $this->getMock('queryFactory');
        $db->expects($this->never())
            ->method('Execute');

        $this->assertFalse(zen_has_product_attributes_downloads_status(1));
    }

    public function testZenHasProductAttributesDownloadsStatusCountsDownloads()
    {
        define('DOWNLOAD_ENABLED', 'true');
//    define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
//    define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');

        $result = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->once())
            ->method('RecordCount')
            ->will($this->returnValue(1));

        global $db;
        $db = $this->getMock('queryFactory');
        $db->expects($this->once())
            ->method('Execute')
            ->will($this->returnValue($result));

        $this->assertTrue(zen_has_product_attributes_downloads_status(1));
    }
}
