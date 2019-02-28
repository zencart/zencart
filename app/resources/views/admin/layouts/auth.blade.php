<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo ADMIN_TITLE; ?></title>
    <link href="includes/template/css/auth.css" rel="stylesheet" type="text/css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
<div id="app" class="app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            @yield('auth')
        </div>
    </div>
</div>
</body>
</html>
