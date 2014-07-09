<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
?>
<?php
  $jspath = $template->get_template_dir('jquery(\.min)?\.js',DIR_WS_TEMPLATE, $current_page_base,'js');
  $jqpath = $jspath . '/jquery.min.js';
?>

<!-- End of page -->
<!-- ================================================== -->
<!-- This javascript placed at the end of the document so pages load faster -->

<?php // note the missing "http" or "https" here ... leaving it out allows it to be called as http or https dynamically, thus not throwing security errors if the wrong mode is used ?>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<?php
  // now check to see if we have a local copy of jQuery and use it as a fall back to local if CDN copy didn't load or if operating offline on a laptop dev machine or something
  if (file_exists($jqpath)) { ?>
<script>window.jQuery || document.write('<scr'+'ipt src="<?php echo $jqpath;?>"><\/scr'+'ipt>');</script><!-- fallback to local if the CDN copy doesn't load -->
<?php } ?>


<?php
// @TODO -- mobile detect? vs Modernizr which is already loaded?
?>

<!--  ================================================== -->
<!-- Load custom javascript items below here -->






<!--  load and activate things like a carousel on home page -->
<?php if ($this_is_home_page) { ?>
 <!-- carousel activation code here -->
<?php } ?>


