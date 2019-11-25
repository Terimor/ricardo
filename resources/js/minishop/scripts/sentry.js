if (js_data.sentry_dsn) {
  js_deps.wait(['vue', 'sentry', 'sentry-vue'], () => {
    Sentry.init({

      dsn: js_data.sentry_dsn,

      integrations: [
        new Sentry.Integrations.Vue({
          Vue,
          attachProps: true,
          logErrors: true,
        }),
      ],

    });
  });
}
