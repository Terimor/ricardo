if (window.Vue) {
  Sentry.init({
    dsn: SentryDSN,
    integrations: [
      new Sentry.Integrations.Vue({
        Vue,
        attachProps: true,
        logErrors: false,
      }),
    ],
  });
} else {
  Sentry.init({
    dsn: SentryDSN,
  });
}
