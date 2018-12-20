<?php
$default = (isset($setting['setting_value'])) ? $setting['setting_value'] : $setting['initial_value'];
?>
<input id="flatpickr-<?php echo $setting['setting_key']; ?>" type="text" value="<?php echo $default; ?>" name="<?php echo $setting['setting_key']; ?>"
       data-input>
<script>
    $("#flatpickr-<?php echo $setting['setting_key']; ?>").flatpickr({dateFormat: "<?php echo DATE_FORMAT; ?>"});
</script>
