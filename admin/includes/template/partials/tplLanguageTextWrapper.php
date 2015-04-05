<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php foreach ($tplVars['leadDefinition']['fields'][$field]['value'] as $languageKey => $entry) {
   require('includes/template/partials/leadInputTypes/tplLanguageText.php');
}
