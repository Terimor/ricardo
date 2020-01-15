@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development' && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <!--[if IE]><script type="text/javascript">if (!Object.assign) { Object.assign = function(o, o1) { Object.keys(o1).forEach(function(n) { o[n] = o1[n]; }); return o; }; }</script><![endif]-->

  <script
    src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  @if (!empty($HasVueApp) || !empty($is_minishop))
    <script
      src="https://browser.sentry-cdn.com/5.6.3/vue.min.js"
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
