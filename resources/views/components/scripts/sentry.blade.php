@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development' && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="https://browser.sentry-cdn.com/5.11.1/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  @if (!empty($HasVueApp) || !empty($is_minishop))
    <script
      src="https://browser.sentry-cdn.com/5.11.1/vue.min.js"
      onload="js_deps.ready('sentry-vue')"
      crossorigin="anonymous"
      async></script>
  @endif

  <script type="text/javascript">
    @if (!empty($HasVueApp) || !empty($is_minishop))
      js_deps.wait(['vue', 'sentry', 'sentry-vue'], function() {
        Sentry.init({
          dsn: '{{ $sentry_dsn }}',
          integrations: [
            new Sentry.Integrations.Vue({
              Vue: Vue,
              attachProps: true,
              logErrors: true,
            }),
          ],
        });
      });
    @else
      js_deps.wait(['sentry'], function() {
        Sentry.init({
          dsn: '{{ $sentry_dsn }}',
        });
      });
    @endif
  </script>

@endif
