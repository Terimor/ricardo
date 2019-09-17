const axios = window.axios;
const fetch = window.fetch;


// execute one time only
if (!window.queryParamsPatch) {
  const initialUrl = new URL(window.location);

  // add js variables
  initialUrl.searchParams.forEach((value, key) => {
    const propName = key + 'js';

    if (window[propName] === undefined) {
      window[propName] = value;
    }
  });

  // patch axios requests
  axios.interceptors.request.use(config => {
    const method = config.method.toLowerCase();

    switch (method) {
      case 'get':
        const myUrl = new URL(config.url, window.location);

        initialUrl.searchParams.forEach((value, key) => {
          if (!myUrl.searchParams.has(key)) {
            myUrl.searchParams.set(key, value);
          }
        });

        config.url = myUrl.pathname + myUrl.search;
        break;
      case 'post':
        config.data = config.data || {};
        
        initialUrl.searchParams.forEach((value, key) => {
          if (config.data[key] === undefined) {
            config.data[key] = value;
          }
        });

        break;
    }

    return config;
  });

  // patch fetch requests
  window.fetch = function(url, options) {
    const method = options && options.method
      ? options.method.toLowerCase()
      : 'get';

    switch (method) {
      case 'get':
        const myUrl = new URL(url, window.location);

        initialUrl.searchParams.forEach((value, key) => {
          if (!myUrl.searchParams.has(key)) {
            myUrl.searchParams.set(key, value);
          }
        });

        url = myUrl.pathname + myUrl.search;
        break;
      case 'post':
        if (options.headers && options.headers['content-type'] === 'application/json') {
          options.body = options.body || '{}';

          try {
            options.body = JSON.parse(options.body);

            initialUrl.searchParams.forEach((value, key) => {
              if (options.body[key] === undefined) {
                options.body[key] = value;
              }
            });

            options.body = JSON.stringify(options.body);
          }
          catch (err) {

          }
        }

        break;
    }

    return fetch(url, options);
  };

  window.queryParamsPatch = true;
}
