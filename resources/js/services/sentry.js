if (window.Sentry && !window.sentryLoaded) {
  Sentry.init({
    dsn: 'https://b7ff672e0d6b4f27a6fbd31fd2ae8a19@sentry.io/1509569',
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
