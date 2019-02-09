<?php require_once('includes/template/common/tplHtmlHead.php'); ?>
</head>
<body class="skin-blue-light">
<div>
    <?php require_once('includes/template/common/tplHeader.php'); ?>
    <div class="container-fluid">
        @yield('content')
    </div>
    <?php require('includes/template/common/tplFooter.php'); ?>
</div>
</body>
</html>
