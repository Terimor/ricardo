import * as cookies from './utils/cookies';
import wait from './utils/wait';


const searchParams = new URL(location).searchParams;


// init Sentry.io service
wait(
  () => !!window.Sentry && !!window.Sentry.init,
  () => window.Sentry.init({
    dsn: window.SentryDSN,
  }),
);


// add js variables from GET params
if (searchParams && searchParams.forEach) {
  searchParams.forEach((value, key) => {
    const propName = key + 'js';

    if (window[propName] === undefined) {
      window[propName] = value;
    }
  });
}


// affiliate variables
window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || 0;
window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || 0;


// js and cookie variables for txid
if (location.pathname === '/splash') {
  const txidFromGet = searchParams.get('txid') || '';
  const txidFromCookie = cookies.getCookie('txid') || '';

  if (txidFromGet.length >= 20) {
    cookies.setCookie('txid', txidFromGet);
  }

  window.txid = window.txidjs = txidFromGet.length >= 20
    ? txidFromGet
    : txidFromCookie.length >= 20
      ? txidFromCookie
      : undefined;
}


// populate links with GET params
function populateLinksWithGetParams() {
  [].forEach.call(document.querySelectorAll('a[href]'), link => {
    const href = link.getAttribute('href');

    if (!href.match(/^https?:\/\//) && !link.href.match(/^mailto:/) && !link.href.match(/^tel:/)) {
      const url = new URL(link.href);

      if (searchParams && searchParams.forEach) {
        searchParams.forEach((value, key) => {
          if (!url.searchParams.has(key)) {
            url.searchParams.set(key, value);
          }
        });
      }

      link.setAttribute('href', url.pathname + url.search + url.hash);
    }
  });
}


// initialize FreshChat custom widget
function initFreshChatWidget() {
  wait(
    () => !!document.querySelector('#fc_frame'),
    () => {
      const parent = document.querySelector('#fc_frame');
      const footer = document.querySelector('.footer__row');
      let image = document.createElement('img');

      if (!parent) {
        return;
      }

      image.src = cdnUrl + '/assets/images/live_chat-full.png';
      image.className = 'freshchat-image';

      image.addEventListener('load', () => {
        parent.appendChild(image);
      });

      image.addEventListener('click', () => {
        fcWidget.open();
      });

      if (footer) {
        const callback = () => {
          setTimeout(() => {
            const html = document.documentElement;

            if (html.clientWidth < 768) {
              const diff = html.scrollHeight - (html.scrollTop + html.clientHeight);
              image.classList[diff < footer.clientHeight ? 'add' : 'remove']('hidden');
            } else {
              image.classList.remove('hidden');
            }
          }, 500);
        };

        window.addEventListener('mousewheel', callback);
        window.addEventListener('resize', callback);
      }
    },
  );
}


// bind static topbar events
function bindStaticTopbarBlock() {
  const parent = document.querySelector('#static-topbar');
  const chatLink = document.querySelector('#static-topbar a.openchat');

  if (parent) {
    parent.classList.remove('hidden');

    wait(
      () => parent.clientHeight > 0,
      () => document.body.style['padding-top'] = parent.clientHeight + 'px',
    );

    window.addEventListener('resize', () => {
      document.body.style['padding-top'] = parent.clientHeight + 'px';
    });
  }

  if (chatLink) {
    chatLink.addEventListener('click', event => {
      event.preventDefault();

      if (window.fcWidget) {
        fcWidget.open();
      }
    });
  }
}


// document ready
function documentReady() {
  document.documentElement.classList.remove('js-hidden');
  populateLinksWithGetParams();
  bindStaticTopbarBlock();
  initFreshChatWidget();
}

if (document.readyState !== 'interactive' && document.readyState !== 'complete') {
  document.addEventListener('DOMContentLoaded', documentReady);
} else {
  documentReady();
}
