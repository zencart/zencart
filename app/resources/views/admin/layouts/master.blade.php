<?php require_once('includes/template/common/tplHtmlHead.php'); ?>
</head>
<body class="skin-blue-light">
<div>
    <?php require_once('includes/template/common/tplHeader.php'); ?>
    <div class="container-fluid">
        @yield('content')
    </div>
    @include('partials/common/footer')
</div>
</body>
</html>
