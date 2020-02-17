<script type="text/javascript">
  (function() {
    @if (Request::get('3ds') && !Request::get('3ds_restore'))
      var stored_query_params = {};

      try {
        stored_query_params = JSON.parse(localStorage.getItem('3ds_params'));
      } catch (err) {

      }

      var url_query_params = [].concat(Object.keys(stored_query_params)).concat(Object.keys(js_query_params)).reduce(function(acc, name) {
        acc[name] = js_query_params[name] !== undefined ? js_query_params[name] : stored_query_params[name];
        return acc;
      }, {});

      var url_search = ['3ds_restore=1']
        .concat(
          Object.keys(url_query_params).map(function(name) {
            return encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || '');
          })
        )
        .join('&');

      location.href = location.pathname + '?' + url_search;
      window['3ds_redirect'] = true;
    @endif

    @if (Request::get('3ds_restore') && Request::get('3ds') === 'success')
      var is_vrtl = location.pathname.substr(1).split('/').shift() === 'vrtl';
      var url_query_params = JSON.parse(JSON.stringify(js_query_params));

      var url_pathname = js_data.product.upsells.length > 0
        ? !is_vrtl
          ? '/thankyou-promos'
          : '/vrtl/upsells'
        : !is_vrtl
          ? '/thankyou'
          : '/vrtl/thankyou';

      url_query_params.order = localStorage.getItem('odin_order_id');
      url_query_params.cur = localStorage.getItem('order_currency');

      delete url_query_params['3ds_restore'];
      delete url_query_params['3ds'];

      var url_search = Object.keys(url_query_params)
        .map(function(name) {
          return encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || '');
        })
        .join('&');

      localStorage.setItem('odin_order_created_at', new Date());
      location.href = url_pathname + '?' + url_search;
    @endif

    @if (Request::get('3ds_restore') && Request::get('3ds') !== 'success')
      localStorage.setItem('order_failed', localStorage.getItem('odin_order_id'));
    @endif
  })();
</script>
