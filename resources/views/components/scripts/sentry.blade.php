@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development' && empty($is_smartbell))

  <script
    src="https://browser.sentry-cdn.com/5.16.0/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  @if (!empty($HasVueApp) || !empty($is_minishop) || !empty($loadVue))
    <script
      src="https://browser.sentry-cdn.com/5.16.0/vue.min.js"
      onload="js_deps.ready('sentry-vue')"
      crossorigin="anonymous"
      async></script>
  @endif

  <script type="text/javascript">
    function SentryAfterInit() {
      Sentry.setExtra('date_deployed', @json(date('Y-m-d H:i:s', filemtime(__FILE__))));
      Sentry.setExtra('date_rendered', @json(date('Y-m-d H:i:s')));
    }

    @if (!empty($HasVueApp) || !empty($is_minishop) || !empty($loadVue))
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

        SentryAfterInit();
      });
    @else
      js_deps.wait(['sentry'], function() {
        Sentry.init({
          dsn: '{{ $sentry_dsn }}',
        });

        SentryAfterInit();
      });
    @endif
  </script>

@endif
