import wait from '../utils/wait';


wait(
  () => !!window.Vue && !!window.Sentry && !!window.Sentry.init && !!window.Sentry.Integrations.Vue,
  () => window.Sentry.init({
    dsn: window.SentryDSN,
    integrations: [
      new window.Sentry.Integrations.Vue({
        Vue: window.Vue,
        attachProps: true,
        logErrors: true,
      }),
    ],
  }),
);
