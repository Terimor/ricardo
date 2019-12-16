import * as cookies from '../utils/cookies';

const searchParams = new URL(location).searchParams;


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

  if (product_id !== checkoutData.product.id) {
    if (product_id) {
      for (let fieldName of cookieFields) {
        cookies.deleteCookie(fieldName);
      }

      for (let fieldName of lsFields) {
        localStorage.removeItem(fieldName);
      }
    }

    localStorage.setItem('product_id', checkoutData.product.id);
  }
}


// direct linking logic
if (location.pathname.startsWith('/checkout')) {
  const txid = searchParams.get('txid') || '';
  const offer_id = +searchParams.get('offer_id') || +searchParams.get('offerid');
  const aff_id = +searchParams.get('aff_id') || +searchParams.get('affid');
  const direct = +searchParams.get('direct');

  if (offer_id > 0 && aff_id > 10 && direct === 1 && txid.length < 20) {
    const iframeURL = new URL('https://track.8xgb.com/aff_c');

    iframeURL.searchParams.set('offer_id', offer_id);
    iframeURL.searchParams.set('aff_id', aff_id);

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
      if (searchParams.has(param)) {
        iframeURL.searchParams.set(param, searchParams.get(param));
      }
    }

    const iframe = document.createElement('iframe');
    iframe.src = iframeURL.toString();
    iframe.style.display = 'none';
    document.body.append(iframe);
  }
}


if (searchParams.get('cur') === '{aff_currency}') {
  delete window.curjs;
}

if (searchParams.get('lang') === '{lang}') {
  delete window.langjs;
}

if (searchParams.get('preload') === '{preload}') {
  window.preloadjs = 3;
}

if (searchParams.get('show_timer') === '{timer}') {
  window.show_timerjs = 1;
}


// js and cookie variables for txid
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


// add tpl body class for checkout
if (location.pathname.startsWith('/checkout')) {
  document.body.classList.add('tpl-' + (searchParams.has('tpl') && searchParams.get('tpl') !== '{tpl}'
    ? searchParams.get('tpl')
    : 'emc1'));
}
