import wait from '../utils/wait';


wait(
  () => !!window.Sentry && !!window.Vue,
  () => Sentry.init({
    dsn: SentryDSN,
    integrations: [
      new Sentry.Integrations.Vue({
        Vue,
        attachProps: true,
        logErrors: false,
      }),
    ],
  }),
);
