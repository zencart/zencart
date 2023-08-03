<?php
/*
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2020 Sep 09 New in v1.5.8-alpha $
 */
?>
<?php echo zen_draw_form('search', basename($PHP_SELF, '.php'), '', 'get', 'class="form-horizontal"', true); ?>
<div class="form-group">
  <?php echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'search', 'class="control-label col-sm-3"'); ?>
  <div class="col-sm-9">
    <div class="input-group">
      <?php echo zen_draw_input_field('search', '', 'class="form-control" id="search"', false, 'search'); ?>
      <span class="input-group-btn">
        <button type="submit" class="btn btn-info"><i class="fa fa-search fa-lg"></i></button>
      </span>
    </div>
  </div>
</div>
<?php
if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
  $keywords = zen_db_prepare_input($_GET['search']);
  ?>
  <div class="form-group">
    <div class="col-sm-3">
      <p class="control-label"><?php echo TEXT_INFO_SEARCH_DETAIL_FILTER; ?></p>
    </div>
    <div class="col-sm-9">
      <div class="input-group">
        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo zen_output_string_protected($keywords); ?></span>
        <span class="input-group-btn">
          <a href="<?php echo zen_href_link(basename($PHP_SELF, '.php')); ?>" class="btn btn-default" role="button" title="<?php echo IMAGE_RESET; ?>"><i class="fa fa-remove fa-lg"></i></a>
        </span>
      </div>
    </div>
  </div>
<?php } ?>
<?php echo '</form>'; ?>