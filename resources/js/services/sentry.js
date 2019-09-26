if (window.Sentry && !window.sentryLoaded) {
  Sentry.init({
    dsn: window.SentryDsn,
    integrations: [
      new Sentry.Integrations.Vue({
        Vue,
        attachProps: true,
        logErrors: false,
      }),
    ],
  });

  window.sentryLoaded = true;
}
