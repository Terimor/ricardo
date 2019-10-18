@if ($HasVueApp)
  @if (Request::is('checkout'))
    <script type="text/javascript">
      (() => {
        const url = new URL(window.location);

        if (url.searchParams.has('3ds') && !url.searchParams.has('3ds_restore')) {
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

          window.stop();
        }
      })();
    </script>
  @endif
@endif
