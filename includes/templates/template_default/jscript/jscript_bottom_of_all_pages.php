<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
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

<?php /** CDN for jQuery core **/ ?>
<script type="text/javascript">window.jQuery || document.write(unescape('%3Cscript type="text/javascript" src="//code.jquery.com/jquery-1.12.0.min.js"%3E%3C/script%3E'));</script>

<?php
// now check to see if we have a local copy of jQuery and use it as a fall back to local if CDN copy didn't load or if operating offline on a laptop dev machine or something
if (file_exists($jqpath)) { ?>
<!-- fallback to local if the CDN copy doesn't load -->
<script type="text/javascript">window.jQuery || document.write(unescape('%3Cscript type="text/javascript" src="<?php echo $jqpath; ?>"%3E%3C/script%3E'));</script>
<?php } ?>


<!--  ================================================== -->
<!-- Load custom javascript items below here -->






<!--  load and activate things like a carousel on home page -->
<?php if ($this_is_home_page) { ?>
 <!-- carousel activation code here -->


 
<?php } ?>


