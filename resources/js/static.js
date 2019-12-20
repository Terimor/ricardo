import searchPopulate from './utils/searchPopulate';
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


// populate links with GET params
function populateLinksWithGetParams() {
  const elements = [].slice.call(document.querySelectorAll('a[href]'));

  elements.forEach(element => {
    const href = element.getAttribute('href');

    if (href && href.substr(0, 1) === '/') {
      element.setAttribute('href', searchPopulate(element.href));
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


// bind returns address dropdown 
function bindReturnsAddressDropdown() { 
  if (location.pathname === '/returns') { 
    const element = document.querySelector('.returns-address'); 
 
    if (element) { 
      const selector = element.querySelector('.selector'); 
      const address = element.querySelector('.address'); 
 
      selector.addEventListener('change', () => { 
        if (!address.innerHTML) { 
          address.classList.add('disable'); 
          address.classList.add('fade'); 
 
          setTimeout(() => { 
            address.innerHTML = selector.querySelector('[value="' + selector.value + '"]').dataset.value || ''; 
            address.classList.remove('disable'); 
            address.classList.remove('fade'); 
          }); 
        } else { 
          address.classList.add('fade'); 
 
          setTimeout(() => { 
            address.innerHTML = selector.querySelector('[value="' + selector.value + '"]').dataset.value || ''; 
            address.classList.remove('fade'); 
          }, 300); 
        } 
      }); 
    } 
  } 
} 


// document ready
function documentReady() {
  populateLinksWithGetParams();
  bindStaticTopbarBlock();
  initFreshChatWidget();
  bindReturnsAddressDropdown();
}

if (document.readyState !== 'interactive' && document.readyState !== 'complete') {
  document.addEventListener('DOMContentLoaded', documentReady);
} else {
  documentReady();
}
