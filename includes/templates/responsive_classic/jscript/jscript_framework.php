<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  New in v1.6.0 $
 */
?>
<script>
if (typeof zcJS == "undefined" || !zcJS) {
  window.zcJS = { name: 'zcJS', version: '0.1.0.0' };
};

zcJS.securityToken = '<?php echo $_SESSION['securityToken']; ?>';

</script>
