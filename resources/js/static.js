import * as cookies from './utils/cookies';
import wait from './utils/wait';


const initialUrl = new URL(location);
const searchParams = initialUrl.searchParams;


// init Sentry.io service
wait(
  () => !!window.Sentry && !!window.Sentry.init,
  () => window.Sentry.init({
    dsn: window.SentryDSN,
  }),
);


// add js variables from GET params
searchParams.forEach((value, key) => {
  const propName = key + 'js';

  if (window[propName] === undefined) {
    window[propName] = value;
  }
});


// affiliate variables
window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || '';
window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || '';


// js and cookie variables for txid
if (location.pathname === '/splash') {
  const txidFromGet = searchParams.get('txid') || '';
  const txidFromCookie = cookies.getCookie('txid') || '';

  if (txidFromGet.length >= 20) {
    document.cookie = 'txid=' + encodeURIComponent(txidFromGet);
  }

  window.txid = window.txidjs = txidFromGet.length >= 20
    ? txidFromGet
    : txidFromCookie.length >= 20
      ? txidFromCookie
      : undefined;
}


// document ready
function documentReady() {
  document.body.classList.remove('js-hidden');
  populateLinksWithGetParams();
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
  document.addEventListener('DOMContentLoaded', documentReady);
} else {
  documentReady();
}
