(function() {


// wait until condition returned true, then execute callback
function wait(condition, callback, timeout = 100) {
  let interval = null;

  function iteration() {
    if (!condition()) {
      return false;
    }

    if (interval) {
      clearInterval(interval);
    }

    callback();

    return true;
  }

  if (!iteration()) {
    interval = setInterval(iteration, timeout);
  }
};


// init Sentry.io service
wait(
  () => !!window.Sentry,
  () => Sentry.init({ dsn: SentryDSN }),
);


// add js variables from GET params
new URL(location).searchParams.forEach((value, key) => {
  if (window[key + 'js'] === undefined) {
    window[key + 'js'] = value;
  }
});


// populate links with GET params
function populateLinksWithGetParams() {
  document.querySelectorAll('a[href]').forEach(link => {
    const href = link.getAttribute('href');

    if (!href.match(/^https?:\/\//) && !link.href.match(/^mailto:/)) {
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


})();
