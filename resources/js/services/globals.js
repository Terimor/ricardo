import * as cookies from '../utils/cookies';


if (location.pathname.startsWith('/checkout')) {
  window.selectedOffer = 0;
  window.selectedPayment = 0;
}


// clear data if product changed
if (location.pathname.startsWith('/checkout')) {
  const product_id = localStorage.getItem('product_id');

  const cookieFields = [
    'txid',
  ];

  const lsFields = [
    'selectedProductData',
    'odin_order_created_at',
    'odin_order_id',
    'order_currency',
    'order_number',
    'order_id',
    'order_failed',
    'subOrder',
    '3ds_params',
    '3ds_ipqs',
  ];

  if (product_id !== js_data.product.id && !window['3ds_redirect']) {
    if (product_id) {
      for (let fieldName of cookieFields) {
        cookies.deleteCookie(fieldName);
      }

      for (let fieldName of lsFields) {
        localStorage.removeItem(fieldName);
      }
    }

    localStorage.setItem('product_id', js_data.product.id);
  }
}


// direct linking logic
if (location.pathname.startsWith('/checkout')) {
  const txid = js_query_params.txid || '';
  const offer_id = +(js_query_params.offer_id || js_query_params.offerid || null);
  const aff_id = +(js_query_params.aff_id || js_query_params.affid || null);
  const direct = +(js_query_params.direct || null);

  if (offer_id > 0 && aff_id > 10 && direct === 1 && txid.length < 20) {
    let iframeURL = 'https://track.8xgb.com/aff_c?offer_id=' + offer_id + '&aff_id=' + aff_id;

    const params = [
      'aff_sub1',
      'aff_sub2',
      'aff_sub3',
      'aff_sub4',
      'aff_sub5',
      'aff_click_id',
      'url_id',
    ];

    for (let param of params) {
      if (js_query_params[param]) {
        iframeURL += '&' + param + '=' + encodeURIComponent(js_query_params[param]);
      }
    }

    const iframe = document.createElement('iframe');

    iframe.src = iframeURL;
    iframe.style.display = 'none';

    document.body.appendChild(iframe);
  }
}


if (js_query_params.cur === '{aff_currency}') {
  delete window.curjs;
}

if (js_query_params.lang === '{lang}') {
  delete window.langjs;
}

if (js_query_params.preload === '{preload}') {
  window.preloadjs = 3;
}

if (js_query_params.show_timer === '{timer}') {
  window.show_timerjs = 1;
}


// js and cookie variables for txid
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

function documentReady() {
  // add tpl body class for checkout
  if (location.pathname.startsWith('/checkout')) {
    const allowed_templates = ['emc1', 'emc1b', 'vmc4', 'smc7', 'smc7p', 'fmc5', 'vmp41', 'vmp42'];

    const tpl = allowed_templates.indexOf(js_query_params.tpl) !== -1
      ? js_query_params.tpl
      : 'emc1';

    document.body.classList.add('tpl-' + tpl);
  }
}

if (document.readyState !== 'interactive' && document.readyState !== 'complete') {
  document.addEventListener('DOMContentLoaded', documentReady);
} else {
  documentReady();
}