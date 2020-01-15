@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development' && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js"
    onload="js_deps.ready('sentry')"
    crossorigin="anonymous"
    async></script>

  <script type="text/javascript">
    @if (!empty($HasVueApp) || !empty($is_minishop))
      js_deps.wait(['vue', 'sentry', 'page-scripts'], function() {
        var script = document.createElement('script');
        script.src = 'https://browser.sentry-cdn.com/5.6.3/vue.min.js';
        script.onload = js_deps.ready('sentry-vue');
        script.crossorigin = 'anonymous';
        script.async = true;
        document.head.appendChild(script);

        js_deps.wait_for(
          function() {
            return !!Sentry.Integrations.Vue;
          },
          function() {
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
          }
        );
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
