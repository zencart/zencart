<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<script id="adminLeadEventHandlers">


        $("#adminLeadContainer").on('change', "#adminLeadMultiCheckbox", function () {
            $('.leadMultiSelect').prop('checked', this.checked);
        });

        $('#adminLeadContainer').on('click', '#adminLeadMultiDelete', function (e) {
            var myCheckboxes = new Array();
            $(".leadMultiSelect:checked").each(function () {
                myCheckboxes.push($(this).val());
            });
            if (myCheckboxes.length > 0) {
                $("#rowMultiDeleteModal").foundation('reveal', 'open');
                $( "#rowMultiDeleteConfirm").unbind( "click" );
                $('#rowMultiDeleteConfirm').on('click', function (e) {
                    $("#rowMultiDeleteModal").foundation('reveal', 'close');
                    zcJS.ajax({
                        url: '<?php echo zen_href_link($_GET['cmd'], zen_get_all_get_params(array('action')) . "action=multiDelete"); ?>',
                        data: {'selected[]': myCheckboxes}
                    }).done(function (response) {
                        if (response.html) {
                            $('#adminLeadItemRows').html(response.html.itemRows);
                            $('#leadPaginator').html(response.html.paginator);
                            $('#leadMultipleActions').html(response.html.ma);
                        }
                    });
                });
            }
        });


        $('#adminLeadContainer').on("click", ".ajaxDataUpdater", function () {
            var action = $(this).data('action')
            var str = $("#lead_filter_form").serializeArray();
            str.push({name: 'field', value: $(this).data('field')});
            str.push({name: 'value', value: $(this).data('value')});
            str.push({name: 'pkey', value: $(this).data('pkey')});
            str.push({name: 'pkeyValue', value: $(this).data('pkeyvalue')});
            str.push({name: 'page', value: <?php echo isset($_GET["page"]) ? $_GET["page"] : 1; ?>});

            zcJS.ajax({
                url: '<?php echo zen_href_link($_GET['cmd'], "action='+action+'+"); ?>',
                data: str
            }).done(function (response) {
                if (response.html) {
                    $('#adminLeadItemRows').html(response.html.itemRows);
                    $('#leadPaginator').html(response.html.paginator);
                    $('#leadMultipleActions').html(response.html.ma);
                }
            });
            return false;
        });

        $("#paginationQueryLimit").on("change", function (e) {
            var str = 'limit=' + $(this).val();
            zcJS.ajax({
                url: '<?php echo zen_href_link($_GET['cmd'], "action=paginationLimit"); ?>',
                type: 'get',
                data: str
            }).done(function (response) {
                if (response.html) {
                    $('#adminLeadItemRows').html(response.html.itemRows);
                    $('#leadPaginator').html(response.html.paginator);
                    $('#leadMultipleActions').html(response.html.ma);
                }
            });
        });

        $(".listLeadFilterInput").on("keyup mouseup", function (form) {
            var str = $("#lead_filter_form").serialize();
            var myform = form;
            zcJS.ajax({
                url: '<?php echo zen_href_link($_GET['cmd'], zen_get_all_get_params(array('action')) . 'action=filter'); ?>',
                data: str
            }).done(function (response) {
                if (response.html) {
                    $('#adminLeadItemRows').html(response.html.itemRows);
                    $('#leadPaginator').html(response.html.paginator);
                    $('#leadMultipleActions').html(response.html.ma);
                }
            });
        });

        $(".reveal-modal").find('.dismiss').click(function () {
            $('a.close-reveal-modal').trigger('click');
        });
</script>
