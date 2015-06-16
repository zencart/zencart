<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0  $
 */

?>
<script>
var dependant = '';
 $('#entry_field_countries_name').change(function () {
 $('#entry_field_<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['dataResponseField']; ?>').val('-1');
 $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').val('-1');
 setTimeout(getDependant, 500)
});

setTimeout(getDependant, 500)
function getDependant()
{

 dependant = $('#entry_field_zone_country_id').val();
 var urlLink = '<?php echo zen_ajax_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')) . 'action=autocompleteGeoZone'); ?>';
 var dataTable = '&dataTable='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['dataTable'];?>';
 var dataSearchField = '&dataSearchField='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['dataSearchField'];?>';
 var dataResponse = '&dataResponse='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['dataResponse'];?>';
 var valueResponse = '&valueResponse='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['valueResponse'];?>';
 <?php if (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['addAllResponse'])) {  ?>
 var addAllResponse = '&addAllResponse='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['addAllResponse'];?>';
 var addAllResponseText = '&addAllResponseText='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['addAllResponseText'];?>';
 var addAllResponseValue = '&addAllResponseValue='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['addAllResponseValue'];?>';
 <?php } ?>
 <?php if (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['extraWhere'])) {  ?>
 var extraWhere = '&extraWhere='+'<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['extraWhere'];?>';
 var extraWhereVal = '&extraWhereVal='+$('#entry_field_zone_country_id').val();
 <?php } ?>
var parameterList = dataTable+dataSearchField+dataResponse+valueResponse;
<?php if (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['addAllResponse'])) {  ?>
parameterList += addAllResponse;
parameterList += addAllResponseText;
parameterList += addAllResponseValue;
<?php } ?>
<?php if (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['extraWhere'])) {  ?>
parameterList += extraWhere;
parameterList += extraWhereVal;
<?php } ?>

 $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').select2({
  placeholder: '<?php echo (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['placeholder']) ? $tplVars['leadDefinition']['fields'][$field]['autocomplete']['placeholder'] : TEXT_AUTOCOMPLETE_DEFAULT_PLACEHOLDER); ?>',
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
  <?php if (isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['extraWhere'])) {  ?>
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

 $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').change(function () {
   var selectedId = $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').select2('data').id;
   var selectedText = $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').select2('data').text;
   $('#entry_field_<?php echo $tplVars['leadDefinition']['fields'][$field]['autocomplete']['dataResponseField']; ?>').val(selectedId);
   $('#<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>').val(selectedText);
 });
}
</script>
