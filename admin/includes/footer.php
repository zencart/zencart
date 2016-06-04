<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Fri Feb 19 20:48:40 2016 -0500 Modified in v1.6.0 $
 */

/**
 * NOTE: Modifying this copyright information is subject to terms of GPL. Check the license for compliance.
 *
 * ALSO NOTE: If you are changing this for a site for a client you're working for, it is only fair to leave the Zen Cart(tm) name and copyright and trademark intact.
 */
?>
<footer>
  <div class="copyrightrow small-12 columns small-centered">
  <a href="http://www.zen-cart.com" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" border="0"></a><br>
  <br>
  E-Commerce Engine Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a><br>
  <a href="<?php echo zen_admin_href_link(FILENAME_SERVER_INFO); ?>"><?php echo zen_get_zcversioninfo('footer'); ?></a>
  </div>
</footer>

<!-- Initialize the Foundation plugins -->
<script>
if (window.Foundation) {
  $(document).foundation();
}
</script>
