@if ($HasVueApp)
  @if (Request::is('checkout'))
    <script type="text/javascript">
      const url = new URL(window.location);

      if (url.searchParams.has('3ds') && !url.searchParams.has('3ds_restore')) {
        const params = window.localStorage.getItem('3ds_params') || '';

        new URLSearchParams(params).forEach((value, key) => {
          if (!url.searchParams.has(key)) {
            url.searchParams.set(key, value);
          }
        });

        url.searchParams.set('3ds_restore', 1);
        window.location.href = url.href;

        window.stop();
      }
    </script>
  @endif
@endif
