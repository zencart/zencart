<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_html_head.php  New in v1.5.7 $
 */
?>
<meta charset="<?php echo CHARSET; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
<link rel="stylesheet" href="includes/css/font-awesome.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="includes/css/jAlert.css">
<link rel="stylesheet" href="includes/css/menu.css">
<link rel="stylesheet" href="includes/css/stylesheet.css">
<?php if (isset($_GET['cmd']) && $_GET['cmd'] != '' && file_exists('includes/css/' . $_GET['cmd'] . '.css')) { ?>
  <link rel="stylesheet" href="includes/css/<?php echo $_GET['cmd']; ?>.css">
<?php } ?>
<?php
// pull in any necessary JS for the page
require(DIR_WS_INCLUDES . 'javascript_loader.php');
