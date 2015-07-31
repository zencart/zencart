<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

/**
 * Class postInstallCatalogChecksTest
 */
class postInstallDatabaseChangesTest extends CommonTestResources
{
    public function testAddSwedishKroner()
    {
        $sql = "INSERT INTO currencies VALUES ('','Swedish Krona','SEK','SEK','',',','','2','1', now());";
        $this->doDbQuery($sql);
    }
}
