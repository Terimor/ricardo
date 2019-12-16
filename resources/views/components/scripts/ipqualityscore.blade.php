@if (Route::is('checkout') || Route::is('checkout_price_set'))

  <script type="text/javascript">
    var IPQ = {
      Callback: function() {
        
      },
    };
  </script>

  <script
    src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/learn.js"
    onload="js_deps.ready('ipqualityscore')"
    async></script>

@endif
