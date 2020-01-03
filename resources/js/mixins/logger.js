export default {

  methods: {

    log_data(text, data, type = 'error') {
      data = {
        url: location.href,
        user_agent: navigator.userAgent,
        ...data,
      },
      fetch('/log-data', {
        method: 'post',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          data,
          error: text,
          'logger-type': type,
        }),
      })
      .catch(err => {

      });
    },

  },

};
