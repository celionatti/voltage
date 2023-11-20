<script>
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
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
    <?php if (isset($_SESSION['__flash_toastr'])) : ?>
        <?php
        $toastr = $_SESSION['__flash_toastr'];
        unset($_SESSION['__flash_toastr']); // Remove the toastr from the session
        ?>
        toastr.<?= $toastr['type'] ?>("<?= $toastr['message'] ?>");
    <?php endif; ?>
</script>