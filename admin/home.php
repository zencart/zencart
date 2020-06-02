<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 New in v1.5.7 $
 */
$version_check_index=true;
require('includes/application_top.php');

$languages = zen_get_languages();
$languages_array = array();
$languages_selected = DEFAULT_LANGUAGE;
for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $_SESSION['language']) {
        $languages_selected = $languages[$i]['code'];
    }
}

if (STORE_NAME == '' || STORE_OWNER =='' || STORE_OWNER_EMAIL_ADDRESS =='' || STORE_NAME_ADDRESS =='') {
    require('index_setup_wizard.php');
} else {
    require('index_dashboard.php');
}
?>
    <footer class="homeFooter">
        <!-- The following copyright announcement is in compliance
        to section 2c of the GNU General Public License, and
        thus can not be removed, or can only be modified
        appropriately.

        Please leave this comment intact together with the
        following copyright announcement. //-->

        <div class="copyrightrow"><a href="https://www.zen-cart.com" rel="noopener" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" /></a><br /><br />E-Commerce Engine Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="https://www.zen-cart.com" rel="noopener" target="_blank">Zen Cart&reg;</a></div><div class="warrantyrow"><br /><br />Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="https://www.zen-cart.com/license/2_0.txt" rel="noopener" target="_blank">GNU General Public License</a><br />
        </div>
    </footer>
    </body>
    </html>
<?php require('includes/application_bottom.php');
