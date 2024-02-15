<?php
/*
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Jul 10 Modified in v2.0.0-alpha1 $
 */
?>
<?php echo zen_draw_form('searchForm', basename($PHP_SELF, '.php'), '', 'get', 'class="form-horizontal"', true); ?>
<div class="form-group row mb-3">
  <?php echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'search', 'class="form-label col-sm-3"'); ?>
  <div class="col-sm-9">
    <div class="input-group">
      <?php echo zen_draw_input_field('search', '', 'class="form-control" id="search"', false, 'search'); ?>
      <span class="input-group-btn">
        <button type="submit" class="btn btn-info"><i class="fa-solid fa-magnifying-glass fa-lg"></i></button>
      </span>
    </div>
  </div>
</div>
<?php
if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
  $keywords = zen_db_prepare_input($_GET['search']);
  ?>
  <div class="form-group row mb-3">
    <div class="col-sm-3">
      <p class="form-label"><?php echo TEXT_INFO_SEARCH_DETAIL_FILTER; ?></p>
    </div>
    <div class="col-sm-9">
      <div class="input-group">
        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo zen_output_string_protected($keywords); ?></span>
        <span class="input-group-btn">
          <a href="<?php echo zen_href_link(basename($PHP_SELF, '.php')); ?>" class="btn btn-secondary" role="button" title="<?php echo IMAGE_RESET; ?>"><i class="fa-solid fa-xmark fa-lg"></i></a>
        </span>
      </div>
    </div>
  </div>
    <?php
    if (file_exists($searchBoxJs)) {
    ?>
        <div class="row">
            <div class="form-horizontal col-xs-6">
                <div class="form-group row mb-3" id="searchRestrictIds">
                    <label for="restrictIDs" class="col-xs-11 form-label"><?= TEXT_INFO_SEARCH_FILTER_RESTRICT_IDS; ?></label>
                    <?= zen_draw_checkbox_field('restrictIDs', '', (!empty($_GET['restrictIDs']) && $_GET['restrictIDs'] === 'on'), '', ' id="restrictIDs" class="col-xs-1"'); ?>
                </div>
            </div>
            <div class="form-horizontal col-xs-6">
                <div class="form-group row mb-3" id="searchTermRepopulate">
                    <label for="repopulateSearch" class="col-xs-11 form-label"><?= TEXT_INFO_SEARCH_FILTER_REPOPULATE; ?></label>
                    <?= zen_draw_checkbox_field('repopulateSearch', '', (!empty($_GET['repopulateSearch']) && $_GET['repopulateSearch'] === 'on'), '', ' id="repopulateSearch" class="col-xs-1"'); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
$extra_form_group = '';
$zco_notifier->notify('NOTIFY_ADMIN_SEARCH_BOX_FORM_GROUP', '', $extra_form_group);
echo $extra_form_group;
?>
<?php echo '</form>'; ?>
