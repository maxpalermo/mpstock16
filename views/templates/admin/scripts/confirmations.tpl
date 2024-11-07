<script type="text/javascript">
    $(function() {
        $("#page-header-desc-mpstock_product-import_orders").on('click', function(e) {
            if (!confirm('Iniziare il processo di importazione ordini (potrebbe durare molto tempo)?')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>