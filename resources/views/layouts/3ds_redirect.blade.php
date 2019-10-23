@if (Request::is('checkout') && Request::get('3ds') && !Request::get('3ds_restore'))
  <script type="text/javascript">
    (() => {
      const url = new URL(location);
      const params = localStorage.getItem('3ds_params') || '';

      new URLSearchParams(params).forEach((value, key) => {
        if (!url.searchParams.has(key)) {
          url.searchParams.set(key, value);
        }
      });

      if (url.searchParams.get('3ds') === 'failure') {
        url.searchParams.set('3ds_restore', 1);
        location.href = url.href;
      }

      if (url.searchParams.get('3ds') === 'success') {
        url.pathname = @if (count($product->upsells) > 0) '/thankyou-promos' @else '/thankyou' @endif ;
        url.searchParams.set('order', localStorage.getItem('odin_order_id'));
        url.searchParams.set('cur', localStorage.getItem('order_currency'));
        url.searchParams.delete('3ds_restore');
        url.searchParams.delete('3ds');

        localStorage.setItem('odin_order_created_at', new Date());
        location.href = url.href;
      }
    })();
  </script>
@endif
