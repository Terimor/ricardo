@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development')

  <script
    src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  @if (isset($is_minishop) || (isset($HasVueApp) && $HasVueApp))
    <script
      src="https://browser.sentry-cdn.com/5.6.3/vue.min.js"
      onload="js_deps.ready('sentry-vue')"
      crossorigin="anonymous"
      async></script>
  @endif

  <script type="text/javascript">
    @if (isset($is_minishop) || (isset($HasVueApp) && $HasVueApp))
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
