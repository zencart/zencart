<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<script>
    $('.rowHandlerRelease_gv').on('click', function () {
        $("#rowReleaseGvModal").modal('show');
        $('#rowGvReleaseConfirm').attr('data-item', $(this).attr('data-item'));
        $('#rowGvReleaseConfirm').on('click', function (e) {
            e.stopImmediatePropagation()
            $("#rowReleaseGvModal").modal('hide');
            zcJS.ajax({
                url: '<?php echo zen_admin_href_link($_GET['cmd'], "action=releaseConfirm"); ?>',
                data: {id: $(this).attr('data-item')}
            }).done(function( response ) {
                if (response.html)
                {
                    $('#adminLeadItemRows').html(response.html.itemRows);
                    $('#leadPaginator').html(response.html.paginator);
                    $('#leadMultipleActions').html(response.html.ma);
                }
            });
            return false
        });

        return false;
    });
</script>
<div class="modal fade" tabindex="-1" role="dialog" id="rowReleaseGvModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo TEXT_TITLE_CONFIRM_RELEASE; ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo TEXT_INFO_CONFIRM_RELEASE; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo TEXT_CANCEL; ?></button>
                <button type="button" class="btn btn-primary" id="rowGvReleaseConfirm"><?php echo TEXT_CONFIRME; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
