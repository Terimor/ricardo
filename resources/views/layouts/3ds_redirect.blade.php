@if (Request::is('checkout') && Request::get('3ds') && !Request::get('3ds_restore'))
  <script type="text/javascript">
    (() => {
      const url = new URL(window.location);
      const params = window.localStorage.getItem('3ds_params') || '';

      new URLSearchParams(params).forEach((value, key) => {
        if (!url.searchParams.has(key)) {
          url.searchParams.set(key, value);
        }
      });

      if (url.searchParams.get('3ds') === 'failure') {
        url.searchParams.set('3ds_restore', 1);
        window.location.href = url.href;
      }

      if (url.searchParams.get('3ds') === 'success') {
        url.pathname = '/thankyou-promos';
        url.searchParams.set('order', localStorage.getItem('odin_order_id'));
        url.searchParams.set('cur', localStorage.getItem('order_currency'));
        localStorage.setItem('odin_order_created_at', new Date());
        url.searchParams.delete('3ds_restore');
        url.searchParams.delete('3ds');

        window.location.href = url.href;
      }
    })();
  </script>
@endif
