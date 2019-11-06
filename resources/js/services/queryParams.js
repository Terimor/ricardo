const initialUrl = new URL(location);
const fetch = window.fetch;


// add cop_id param if price set exists
if (/^\/checkout\/.+/.test(location.pathname)) {
  initialUrl.searchParams.set('cop_id', location.pathname.split('/')[2]);
}


// populate links with GET params
function populateLinksWithGetParams() {
  document.querySelectorAll('a[href]').forEach(link => {
    const href = link.getAttribute('href');

    if (!href.match(/^https?:\/\//) && !link.href.match(/^mailto:/) && !link.href.match(/^tel:/)) {
      const url = new URL(link.href);

      initialUrl.searchParams.forEach((value, key) => {
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
        const myUrl = new URL(url, location);

        initialUrl.searchParams.forEach((value, key) => {
          if (!myUrl.searchParams.has(key)) {
            myUrl.searchParams.set(key, value);
          }
        });

        url = myUrl.pathname + myUrl.search;
        break;
      case 'post':
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

        break;
    }
  }

  return fetch.call(this, url, options);
};
