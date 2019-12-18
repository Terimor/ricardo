@if (Route::is('checkout') || Route::is('checkout_price_set'))

  @if (Request::get('3ds') && !Request::get('3ds_restore'))
    <script type="text/javascript">
      (() => {
        const url = new URL(location);

        let url_query_params = url.search
          .substr(1).split('&').filter(item => !!item).map(item => item.split('='))
          .reduce((acc, item) => {
            acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
            return acc;
          }, {});

        let stored_query_params = localStorage.getItem('3ds_params') || '{}';

        try {
          stored_query_params = JSON.parse(stored_query_params);
        } catch (err) {
          stored_query_params = {};
        }

        for (let name of Object.keys(stored_query_params)) {
          if (!url_query_params[name]) {
            url_query_params[name] = stored_query_params[name];
          }
        }

        let url_search = ['3ds_restore=1'];

        for (let name of Object.keys(url_query_params)) {
          url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
        }

        location.href = url.pathname + '?' + url_search.join('&');
        window['3ds_redirect'] = true;
      })();
    </script>
  @endif

  @if (Request::get('3ds') === 'success' && Request::get('3ds_restore'))
    <script type="text/javascript">
      (() => {
        const url = new URL(location);

        let url_query_params = url.search
          .substr(1).split('&').filter(item => !!item).map(item => item.split('='))
          .reduce((acc, item) => {
            acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
            return acc;
          }, {});

        localStorage.setItem('odin_order_created_at', new Date());
        url.pathname = '{{ count($product->upsells) > 0 ? '/thankyou-promos' : '/thankyou' }}';

        url_query_params.order = localStorage.getItem('odin_order_id');
        url_query_params.cur = localStorage.getItem('order_currency');
        delete url_query_params['3ds_restore'];
        delete url_query_params['3ds'];

        let url_search = [];

        for (let name of Object.keys(url_query_params)) {
          url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
        }

        location.href = url.pathname + '?' + url_search.join('&');
      })();
    </script>
  @endif

  @if (Request::get('3ds') === 'failure' && Request::get('3ds_restore'))
    <script type="text/javascript">
      (() => {
        localStorage.setItem('order_failed', localStorage.getItem('odin_order_id'));
      })();
    </script>
  @endif

@endif
