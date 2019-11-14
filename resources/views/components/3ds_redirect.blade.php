@if (Route::is('checkout') || Route::is('checkout_price_set'))

  @if (Request::get('3ds') && !Request::get('3ds_restore'))
    <script type="text/javascript">
      (() => {
        const url = new URL(location);
        const params = localStorage.getItem('3ds_params') || '';

        new URLSearchParams(params).forEach((value, key) => {
          if (!url.searchParams.has(key)) {
            url.searchParams.set(key, value);
          }
        });

        url.searchParams.set('3ds_restore', 1);
        location.href = url.href;
      })();
    </script>
  @endif

  @if (Request::get('3ds') === 'success' && Request::get('3ds_restore'))
    <script type="text/javascript">
      (() => {
        const url = new URL(location);

        localStorage.setItem('odin_order_created_at', new Date());
        url.pathname = @if (count($product->upsells) > 0) '/thankyou-promos' @else '/thankyou' @endif ;
        url.searchParams.set('order', localStorage.getItem('odin_order_id'));
        url.searchParams.set('cur', localStorage.getItem('order_currency'));
        url.searchParams.delete('3ds_restore');
        url.searchParams.delete('3ds');

        location.href = url.href;
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
