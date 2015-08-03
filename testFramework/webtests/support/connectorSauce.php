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
class baseSeleniumTestClass extends Sauce\Sausage\WebDriverTestCase
{
    public static $browsers = array(
        array(
            'browserName' => 'firefox',
            'desiredCapabilities' => array(
                'version' => '39',
                'platform' => 'windows 8.1',
            )
        ),
    );

    public function setUpPage()
    {
        $this->timeouts()->implicitWait(15000);
    }
}
