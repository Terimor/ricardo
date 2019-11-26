@if (isset($sentry_dsn) && config('app.env') !== 'local' && config('app.env') !== 'development')

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

  <script type="text/javascript">
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
  </script>

@endif
