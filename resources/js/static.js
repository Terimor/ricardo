import * as cookies from './utils/cookies';
import wait from './utils/wait';


// js and cookie variables for txid
if (location.pathname === '/splash') {
  const txidFromGet = js_query_params.txid || '';
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


export function searchPopulate(pathname, exclude = []) {
  const url = new URL(pathname, location);

  let url_query_params = url.search
    .substr(1).split('&').filter(item => !!item).map(item => item.split('='))
    .reduce((acc, item) => {
      acc[decodeURIComponent(item[0])] = decodeURIComponent(item[1]);
      return acc;
    }, {});

  for (let name of Object.keys(js_query_params)) {
    if (!url_query_params[name] && !exclude.includes(name)) {
      url_query_params[name] = js_query_params[name];
    }
  }

  let url_search = [];

  for (let name of Object.keys(url_query_params)) {
    url_search.push(encodeURIComponent(name) + '=' + encodeURIComponent(url_query_params[name] || ''));
  }

  url_search = url_search.length > 0
    ? '?' + url_search.join('&')
    : '';

  return url.pathname + url_search;
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

      image.src = js_data.cdn_url + '/assets/images/live_chat-full.png';
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
  populateLinksWithGetParams();
  bindStaticTopbarBlock();
  initFreshChatWidget();
}

if (document.readyState !== 'interactive' && document.readyState !== 'complete') {
  document.addEventListener('DOMContentLoaded', documentReady);
} else {
  documentReady();
}
