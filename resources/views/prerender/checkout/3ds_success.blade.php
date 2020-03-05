@include('components.layout.js_data')

<script type="text/javascript">
  @if (count($product->upsells) > 0)
    @if (!$is_vrtl_page)
      var url_pathname = '/thankyou-promos';
    @else
      var url_pathname = '/vrtl/upsells';
    @endif
  @else
    @if (!$is_vrtl_page)
      var url_pathname = '/thankyou';
    @else
      var url_pathname = '/vrtl/thankyou';
    @endif
  @endif

  var url_query_params = JSON.parse(JSON.stringify(js_query_params));

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
</script>
