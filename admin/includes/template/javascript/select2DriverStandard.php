<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<script>
var urlLink = '<?php echo zen_ajax_href_link($tplVars['cmd'], zen_get_all_get_params(array('action')) . 'action=fillByLookup'); ?>';
var dataTable = '&dataTable='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['dataTable'];?>';
var dataSearchField = '&dataSearchField='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['dataSearchField'];?>';
var dataResponse = '&dataResponse='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['dataResponse'];?>';
var valueResponse = '&valueResponse='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['valueResponse'];?>';
<?php if (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['addAllResponse'])) {  ?>
var addAllResponse = '&addAllResponse='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['addAllResponse'];?>';
var addAllResponseText = '&addAllResponseText='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['addAllResponseText'];?>';
var addAllResponseValue = '&addAllResponseValue='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['addAllResponseValue'];?>';
<?php } ?>
<?php if (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['extraWhere'])) {  ?>
var extraWhere = '&extraWhere='+'<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['extraWhere'];?>';
var extraWhereVal = '&extraWhereVal='+$('#entry_field_zone_country_id').val();
<?php } ?>
var parameterList = dataTable+dataSearchField+dataResponse+valueResponse;
<?php if (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['addAllResponse'])) {  ?>
parameterList += addAllResponse;
parameterList += addAllResponseText;
parameterList += addAllResponseValue;
<?php } ?>
<?php if (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['extraWhere'])) {  ?>
parameterList += extraWhere;
parameterList += extraWhereVal;
<?php } ?>
$('#<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>').select2({
 placeholder: '<?php echo (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['placeholder']) ? $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['placeholder'] : TEXT_FILLBYLOOKUP_DEFAULT_PLACEHOLDER); ?>',
 dropdownAutoWidth: true,
 ajax: {
   url: urlLink + parameterList,
   dataType: 'json',
   quietMillis: 100,
   data: function (term, page) {
    return {
        term: term, //search term
        page_limit: 10 // page size
    };
   },
   results: function (data, page) {
    return { results: data.results };
   }
},
initSelection: function (element, callback) {
 parameterList = dataTable+dataSearchField+dataResponse+valueResponse+'&exactMatch=true';
 <?php if (isset($tplVars['pageDefinition']['fields'][$field]['fillByLookup']['extraWhere'])) {  ?>
 parameterList += extraWhere;
 parameterList += extraWhereVal;
 <?php } ?>

 var id=$(element).val();
 if (id!=="") {
  zcJS.ajax({
   url: urlLink + parameterList+'&term='+id,
   data: {},
   type: 'GET'
 }).done(function (response) {
  callback(response.results[0]);
 });
  }
}
});
$('#<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>').change(function () {
  var selectedId = $('#<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>').select2('data').id;
  var selectedText = $('#<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>').select2('data').text;
  $('#entry_field_<?php echo $tplVars['pageDefinition']['fields'][$field]['fillByLookup']['dataResponseField']; ?>').val(selectedId);
  $('#<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>').val(selectedText);

});
</script>
