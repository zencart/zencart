<?php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ADMIN_TITLE; ?></title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <?php foreach ($tplVars['cssList'] as $entry) { ?>
        <link rel="stylesheet" type="text/css"
              href="<?php echo $entry['href']; ?>" <?php echo(isset($entry['id']) ? 'id="' . $entry['id'] . '"' : ''); ?> <?php echo(isset($entry['media']) ? 'media="' . $entry['media'] . '"' : ''); ?>>
    <?php } ?>
    <?php if (isset($tplVars['jscriptVars'])) { ?>
        <script>
            <?php foreach ($tplVars['jscriptVars'] as $jsName => $jsValue) { ?>
            var <?php echo $jsName; ?> = '<?php echo $jsValue; ?>'
            <?php } ?>
        </script>
    <?php } ?>
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <?php /** CDN for jQuery core **/ ?>
    <script src="//code.jquery.com/jquery-2.2.4.min.js"></script>
    <script>window.jQuery || document.write('<script src="includes/template/javascript/jquery-2.2.4.min.js"><\/script>');</script>
    <script src="includes/template/javascript/zcJSFramework.js"></script>
    <link rel="stylesheet" type="text/css" href="includes/template/css/jquery-ui.min.css" id="jQueryUIThemeCSS">
    <?php /** CDN for jQuery UI components **/ ?>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>window.jQuery.Widget || document.write('<script src="includes/template/javascript/jquery-ui.min.js"><\/script>');</script>
    <script src="includes/template/javascript/jquery-ui-i18n.min.js"></script>
    <script src="includes/template/AdminLTE2/bootstrap/js/bootstrap.min.js"></script>
    <script src="includes/template/AdminLTE2/dist/js/app.js"></script>

    <!-- Ionicons
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="includes/template/javascript/select2-master/select2.css" id="select2CSS">
    <link rel="stylesheet" type="text/css" href="includes/template/css/menu.css" id="menuCSS">
    <link rel="stylesheet" href="includes/template/AdminLTE2/plugins/daterangepicker/daterangepicker-bs3.css">
    <script src="includes/template/javascript/select2-master/select2.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
</head>
