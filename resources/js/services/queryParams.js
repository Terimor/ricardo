const initialUrl = new URL(window.location);
const fetch = window.fetch;


// add js variables from GET params
initialUrl.searchParams.forEach((value, key) => {
  const propName = key + 'js';

  if (window[propName] === undefined) {
    window[propName] = value;
  }
});


// affiliate variables
window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || '';
window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || '';


// populate links with GET params
function populateLinksWithGetParams() {
  document.querySelectorAll('a[href]').forEach(link => {
    const href = link.getAttribute('href');

    if (!href.match(/^https?:\/\//) && !link.href.match(/^mailto:/) && !link.href.match(/^tel:/)) {
      const url = new URL(link.href);

      new URL(location).searchParams.forEach((value, key) => {
        if (!url.searchParams.has(key)) {
          url.searchParams.set(key, value);
        }
      });

      link.setAttribute('href', url.pathname + url.search + url.hash);
    }
  });
}

if (document.readyState !== 'interactive' && document.readyState !== 'complete') {
  document.addEventListener('DOMContentLoaded', populateLinksWithGetParams);
} else {
  populateLinksWithGetParams();
}


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
