import searchPopulate from '../utils/searchPopulate';


const fetch = window.fetch;


// add cop_id param if price set exists
if (/^\/checkout\/.+/.test(location.pathname)) {
  js_query_params.cop_id = location.pathname.split('/')[2];
}


// populate links with GET params
function populateLinksWithGetParams() {
  const elements = [].slice.call(document.querySelectorAll('a[href]'));

  for (let element of elements) {
    const href = element.getAttribute('href');

    if (href && href.substr(0, 1) === '/') {
      element.setAttribute('href', searchPopulate(element.href));
    }
  }
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

  if (url.substr(0, 1) === '/') {
    switch (method) {
      case 'get':
        url = searchPopulate(url);
        break;
      case 'post':
        options.body = options.body || '{}';

        try {
          options.body = JSON.parse(options.body);

          for (let name of Object.keys(js_query_params)) {
            if (options.body[name] === undefined) {
              options.body[name] = js_query_params[name];
            }
          }

          options.body = JSON.stringify(options.body);
        }
        catch (err) {

        }

        break;
    }
  }

  if (!fetch) {
    return Promise.reject(new Error('fetch method is not supported'));
  }

  return fetch.call(this, url, options);
};
