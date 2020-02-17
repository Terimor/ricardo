@if ((Route::is('checkout') || Route::is('checkout_price_set')) && !$is_new_engine)

  @if (Request::get('3ds') && !Request::get('3ds_restore'))
    <script type="text/javascript">
      (function() {
        var url_query_params = JSON.parse(JSON.stringify(js_query_params));
        var stored_query_params = localStorage.getItem('3ds_params') || '{}';

        try {
          stored_query_params = JSON.parse(stored_query_params);
        } catch (err) {
          stored_query_params = {};
        }

        Object.keys(stored_query_params).forEach(function(name) {
          if (!url_query_params[name]) {
            url_query_params[name] = stored_query_params[name];
          }
        });

        var url_search = ['3ds_restore=1'];

        Object.keys(url_query_params).forEach(function(name) {
          url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
        });

        location.href = location.pathname + '?' + url_search.join('&');
        window['3ds_redirect'] = true;
      })();
    </script>
  @endif

  @if (Request::get('3ds') === 'success' && Request::get('3ds_restore'))
    <script type="text/javascript">
      (function() {
        var url_query_params = JSON.parse(JSON.stringify(js_query_params));
        var url_pathname = '{{ count($product->upsells) > 0 ? '/thankyou-promos' : '/thankyou' }}';

        localStorage.setItem('odin_order_created_at', new Date());
        url_query_params.order = localStorage.getItem('odin_order_id');
        url_query_params.cur = localStorage.getItem('order_currency');
        delete url_query_params['3ds_restore'];
        delete url_query_params['3ds'];

        var url_search = [];

        Object.keys(url_query_params).forEach(function(name) {
          url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
        });

        location.href = url_pathname + '?' + url_search.join('&');
      })();
    </script>
  @endif

  @if (Request::get('3ds') !== 'success' && Request::get('3ds_restore'))
    <script type="text/javascript">
      (function() {
        localStorage.setItem('order_failed', localStorage.getItem('odin_order_id'));
      })();
    </script>
  @endif

@endif
