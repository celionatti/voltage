<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Auth Layout | Auth</title>
    <link type="text/css" rel="stylesheet" href="<?= getPackageAssets("fontawesome/all.min.css") ?>">
    <link type="text/css" rel="stylesheet" href="<?= getPackageAssets("volt/volt.css") ?>">
    <link type="text/css" rel="stylesheet" href="<?= getPackageAssets("bootstrap/css/bootstrap.min.css") ?>">
    <link type="text/css" rel="stylesheet" href="<?= getPackageAssets("toastr/toastr.min.css") ?>">
  </head>
  <body class="sb-nav-fixed">
  <?= packagePartial("volt-header") ?>
    <div id="layoutSidenav">
        <?= packagePartial("volt-sidebar") ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <!-- Your Content goes in here. -->
                    <?php $this->includeTemplate('volt/index'); ?>
                </div>
            </main>
            <?= packagePartial("volt-footer") ?>
        </div>
    </div>

    <script src="<?= getPackageAssets("jquery/jquery-3.6.3.min.js") ?>"></script>
    <script src="<?= getPackageAssets('toastr/toastr.min.js'); ?>"></script>
    <script src="<?= getPackageAssets('volt/volt.js'); ?>"></script>
    <script src="<?= getPackageAssets('bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        <?php if (isset($_SESSION['__flash_toastr'])): ?>
              <?php
              $toastr = $_SESSION['__flash_toastr'];
              unset($_SESSION['__flash_toastr']); // Remove the toastr from the session
              ?>
              toastr.<?= $toastr['type'] ?>("<?= $toastr['message'] ?>");
        <?php endif; ?>
    </script>
  </body>
</html>
