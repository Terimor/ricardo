import * as cookies from '../utils/cookies';

const searchParams = new URL(location).searchParams;


// clear cookies if product changed
if (location.pathname === '/checkout') {
  const product_id = cookies.getCookie('product_id');

  if (product_id !== checkoutData.product.id) {
    localStorage.clear();
    cookies.clearCookies({ except: ['XSRF-TOKEN'] });
    cookies.setCookie('product_id', checkoutData.product.id);
  }
}


// direct linking logic
if (location.pathname === '/checkout') {
  const txid = searchParams.get('txid') || '';
  const offer_id = +searchParams.get('offer_id');
  const aff_id = +searchParams.get('aff_id');
  const direct = +searchParams.get('direct');

  if (offer_id > 0 && aff_id > 10 && direct === 1 && txid.length < 20) {
    const iframe = document.createElement('iframe');
    iframe.src = `https://lai.go2cloud.org/aff_c?offer_id=${offer_id}&aff_id=${aff_id}`;
    iframe.style.display = 'none';
    document.body.append(iframe);
  }
}


// js variables from GET params
searchParams.forEach((value, key) => {
  const propName = key + 'js';

  if (window[propName] === undefined) {
    window[propName] = value;
  }
});


// js variables for affiliate
window.aff_idjs = window.affidjs = window.aff_idjs || window.affidjs || '';
window.offer_idjs = window.offeridjs = window.offer_idjs || window.offeridjs || '';


if (searchParams.get('cur') === '{aff_currency}') {
  delete window.curjs;
}

if (searchParams.get('lang') === '{lang}') {
  delete window.langjs;
}

if (searchParams.get('preload') === '{preload}') {
  window.preloadjs = 3;
}

if (searchParams.get('show_timer') === '{show_timer}') {
  window.show_timerjs = 1;
}


// js and cookie variables for txid
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