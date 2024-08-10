<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?? 'SimpleTine' ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="/assets/simpletine/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="/assets/simpletine/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <?php if (isset($is_datatables) && $is_datatables) :?>
    <!-- DataTables -->
    <link rel="stylesheet" href="/assets/simpletine/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/simpletine/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <?php endif; ?>

    <!-- Theme style -->
    <link rel="stylesheet" href="/assets/simpletine/css/adminlte.min.css">

    <!-- CSS Files -->
    <?php if (isset($css) && is_array($css)) {
        foreach($css as $file) {
            echo '<link rel="stylesheet" href="' . $file . '">';
        }
    } ?>
</head>