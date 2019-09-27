const initialUrl = new URL(window.location);
const fetch = window.fetch;


// add js variables
initialUrl.searchParams.forEach((value, key) => {
  const propName = key + 'js';

  if (window[propName] === undefined) {
    window[propName] = value;
  }
});


// patch fetch requests
window.fetch = function(url, options = {}) {
  const method = options.method
    ? options.method.toLowerCase()
    : 'get';

  if (!url.match(/^https?:\/\//)) {
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
  }

  return fetch.call(this, url, options);
};
