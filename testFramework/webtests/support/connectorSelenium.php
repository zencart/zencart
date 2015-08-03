<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

/**
 * Class baseSeleniumTestClass
 */
class baseSeleniumTestClass extends PHPUnit_Extensions_Selenium2TestCase
{
    use \CompatibilityTestCase;

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('/');
    }

    public function setUpPage()
    {
        $this->timeouts()->implicitWait(15000);
    }
}
