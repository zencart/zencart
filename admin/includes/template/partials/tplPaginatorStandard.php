<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div  id="leadPaginator">
<?php if ($tplVars['listingBox']['paginator']['hasItems']) { ?>
<ul class="pagination">
<?php if (!$tplVars['listingBox']['paginator']['flagHasPrevious']) { ?>
<li class="arrow unavailable"><a href="#">&laquo;</a></li>
<?php } else { ?>
<li class="arrow"><a class="paginatorItemLink" data-value="<?php echo($tplVars['listingBox']['paginator']['prevPage']) ?>" data-name="<?php echo($tplVars['listingBox']['paginator']['pagingVarName']) ?>" href="<?php echo($tplVars['listingBox']['paginator']['previousLink']) ?>">&laquo;</a></li>
<?php } ?>
<?php foreach($tplVars['listingBox']['paginator']['linkList'] as $item) { ?>
<li <?php echo ($item['isCurrent']) ? 'class="current"' : ''; ?>><a class="paginatorItemLink" data-value="<?php echo($item['itemNumber']) ?>" data-name="<?php echo($tplVars['listingBox']['paginator']['pagingVarName']) ?>" href="<?php echo($item['itemLink']) ?>"><?php echo($item['itemNumber']) ?></a></li>
<?php } ?>
<?php if (!$tplVars['listingBox']['paginator']['flagHasNext']) { ?>
<li class="arrow unavailable"><a href="#">&raquo;</a></li>
<?php } else { ?>
<li class="arrow"><a class="paginatorItemLink" data-value="<?php echo($tplVars['listingBox']['paginator']['nextPage']) ?>" data-name="<?php echo($tplVars['listingBox']['paginator']['pagingVarName']) ?>" href="<?php echo($tplVars['listingBox']['paginator']['nextLink']) ?>">&raquo;</a></li>
<?php } ?>
</ul>
<?php } ?>
<?php if ($tplVars['listingBox']['paginator']['totalItemCount']) { ?>
<div class="navSplitPagesResult"><?php echo(sprintf($tplVars['listingBox']['paginator']['navLinkText'], $tplVars['listingBox']['paginator']['fromItem'], $tplVars['listingBox']['paginator']['toItem'], $tplVars['listingBox']['paginator']['totalItems'])) ?></div>
<?php } ?>
</div>
<script>
$(".paginatorItemLink").on('click', function() {
  var str = $("#lead_filter_form").serializeArray();
    var pn= $(this).data('name');
    var pv = $(this).data('value');
    str.push ({name: pn, value: pv});
  zcJS.ajax({
      url: '<?php echo zen_href_link($_GET['cmd'], 'action=paginator'); ?>',
      data: str
    }).done(function( response ) {
      if (response.html)
      {
        $('#adminLeadItemRows').html(response.html.itemRows);
        $('#leadPaginator').html(response.html.paginator);
        $('#leadMultipleActions').html(response.html.ma);
               }
    });
return false;
});
</script>
