@if (config('app.env') !== 'local' && config('app.env') !== 'development')

  <script type="text/javascript">
    js_data.sentry_dsn = '{{ $sentry_dsn }}';
  </script>

  <script
    src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  <script
    src="https://browser.sentry-cdn.com/5.6.3/vue.min.js"
    onload="js_deps.ready('sentry-vue')"
    crossorigin="anonymous"
    async></script>

@endif
