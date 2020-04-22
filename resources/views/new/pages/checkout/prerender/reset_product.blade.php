<script type="text/javascript">
  (function() {
    var product_id = localStorage.getItem('product_id');

    var cookie_fields = [
      'txid',
    ];

    var ls_fields = [
      'selectedProductData',
      'odin_order_created_at',
      'odin_order_id',
      'order_currency',
      'order_number',
      'order_id',
      'order_failed',
      'subOrder',
      '3ds_params',
      '3ds_ipqs',
    ];

    if (product_id !== js_data.product.id) {
      if (product_id) {
        cookie_fields.forEach(function(name) {
          document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Secure';
        });

        ls_fields.forEach(function(name) {
          localStorage.removeItem(name);
        });
      }

      localStorage.setItem('product_id', js_data.product.id);
    }
  })();
</script>
