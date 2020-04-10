import fingerprint from '../services/fingerprintjs2';
import { getCountOfInstallments } from './installments';
import { goTo } from './goTo';
import { t } from './i18n';
import { queryParams } from  './queryParams';
import { getRandomInt } from '../utils/common';
import { goToThankYou } from './checkout';

export const getRadioHtml = ({
   discountName,
   newPrice,
   textComposite,
   totalQuantity,
   price,
   discountText,
   currency = '$',
   installments,
   idx,
   pricePerUnit
}) => {
  const isEmc1b = queryParams().tpl === 'emc1b';

  const isSellOutArray = queryParams().sellout
    ? queryParams().sellout.split(',')
    : [];

  const isSoldOut = isSellOutArray.indexOf(String(totalQuantity)) !== -1;

  const formattedPrice = discountName ? `${pricePerUnit[installments]}/${t('checkout.unit')}` : pricePerUnit[installments];

  const currentPrice = isEmc1b
    ? formattedPrice
    : getCountOfInstallments(installments) + newPrice.toLocaleString();

  if (js_data.product.labels && js_data.product.labels[totalQuantity] && js_data.product.unit_qty > 1) {
    discountName = js_data.product.labels[totalQuantity];
  }

  return (
    `${discountName
      ? `<p class="label-container-radio__best-seller">
                <span class="label-container-radio__best-seller__name">${discountName}</span>
                ${ isSoldOut ? `<span class="label-container-radio__best-seller__soldout red">${t('checkout.sold_out')}</span>` : ''}
                <span class="label-container-radio__best-seller__price">${currentPrice}</span>
            </p>`
      : ''}

  
        ${idx === 1 ? '<img class="lazy share" data-src="' + js_data.cdn_url + '/assets/images/share.png">' : ''}
    
        <p class="label-container-radio__name-price">
                  
          <span class="label-container-radio__name-price__name">${textComposite}</span>
          
          ${(!discountName && isSoldOut) ? `<span class='label-container-radio__name-price__soldout red'>${t('checkout.sold_out')}</span>` : ''}

          ${!isEmc1b
            ? `<span ${discountName ? 'class="strike"' : 'class="red"'}>
                    ${getCountOfInstallments(installments) + (!discountName ? newPrice : price).toLocaleString()}
               </span>`
            : `${discountName ? '' : currentPrice}`}
        </p>

        ${!js_data.product.labels || !js_data.product.labels[totalQuantity] || js_data.product.unit_qty > 1
          ? `<p class="label-container-radio__discount">
              <span class="discount-text${idx === 1 ? ' red' : ''}">${discountText}</span>
              <span class="strike">
                ${!isEmc1b && !discountName
                    ? getCountOfInstallments(installments) + price.toLocaleString()
                    : ''
                }
              </span>
            </p>`
          : ''}
      `)
};


export function * getNotice ({
  users,
  cities,
  usersActive,
  bestsellerText,
  paypal,
}) {
  let index = 0
  const messageMap = {
    paypal: `<div class="recently-notice recently-notice_paypal">
        <div class="recently-notice__left">
          <img class="lazy" data-src="${js_data.cdn_url}/assets/images/paypal232.png" alt="PayPal">
        </div>
        <div class="recently-notice__right">
          <p>${t('checkout.paypal.risk_free')}</p>
          <img class="lazy" data-src="${js_data.cdn_url}/assets/images/paypal-highq.png" alt="PayPal">
        </div>
      </div>
    `,
    usersActive: `
      <div class="recently-notice recently-notice_user-active">
        <div class="recently-notice__left">
          <i class="fa fa-user"></i>
        </div>
        <div class="recently-notice__right">
          <p>${t('checkout.notification.users_active', { count: usersActive })}</p>
        </div>
      </div>
    `,
    bestsellerText: `
      <div class="recently-notice recently-notice_high-demand">
        <div class="recently-notice__left">
          <i class="fa fa-user"></i>
        </div>
        <div class="recently-notice__right">
          <p>${bestsellerText}</p>
        </div>
      </div>
    `,
    paypalNotice: `<b>paypalNotice</b>`
  }

  let keyQueue = [
    'recentlyBought',
    'recentlyBought',
    'usersActive',
    paypal ? 'paypal' : null,
    'recentlyBought',
    'recentlyBought',
    'bestsellerText'
  ]

  keyQueue = keyQueue.filter(name => !!name);

  let lastIndex = 0

  while (true) {
    if (lastIndex < keyQueue.length - 1) {
      if (keyQueue[lastIndex] === 'recentlyBought') {
        if (users.length === index) {
          index = 0;
        }

        const arr = [1, 3, 5];
        const quantity = arr[getRandomInt(0, 2)];

        if(users[index] && cities[index]) {
            let just_bought = t('checkout.notification.just_bought', {
              first_name: users[index],
              city: cities[index] || cities[0],
              count: quantity,
              product: js_data.product.product_name,
            });

            if (js_data.product.unit_qty > 1) {
              just_bought += ' ' + t('product.unit_qty.total', {
                count: quantity * js_data.product.unit_qty,
              });
            }

            yield `<div class="recently-notice">
              <div class="recently-notice__left">
                <img class="lazy" data-src="${js_data.product.image[0]}" alt="">
              </div>
              <div class="recently-notice__right">
                <p>${just_bought}</p>
              </div>
            </div>
            `
        }

        index++;

      } else {
        yield messageMap[keyQueue[lastIndex]]
      }
      lastIndex++
    } else {
      index = 0
      yield messageMap[keyQueue[lastIndex]]
      lastIndex = 0
    }
  }
}


let stored_paypal_order_id = null;

export function paypalCreateOrder ({
  xsrfToken = document.head.querySelector('meta[name="csrf-token"]').content,
  sku_code,
  sku_quantity,
  is_warranty_checked,
  page_checkout = document.location.href,
  cur,
  offer = js_query_params.offer || null,
  affiliate = js_query_params.affiliate || null,
  ipqsResult,
}) {
  let f = null;

  return Promise.resolve()
    .then(fingerprint)
    .then(hash => f = hash)
    .then(() => fetch('/paypal-create-order', {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': xsrfToken,
        'content-type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        sku_code,
        sku_quantity,
        is_warranty_checked,
        order: stored_paypal_order_id || '',
        page_checkout,
        cur,
        offer,
        affiliate,
        ipqs: ipqsResult,
        f,
      }),
    }))
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => {
      if (res.odin_order_id) {
        stored_paypal_order_id = res.odin_order_id;
      }

      if (res.error) {
        if (res.error.code === 10008) {
          res.paypalPaymentError = t(res.error.message.phrase, res.error.message.args);
        }
      } else if (res.odin_order_id) {
        localStorage.setItem('odin_order_id', res.odin_order_id);
        localStorage.setItem('order_currency', res.order_currency);
        localStorage.setItem('order_number', res.order_number);
        localStorage.setItem('order_id', res.id);
      }

      return res;
    })
    .catch(err => {

    });
}

export function paypalOnApprove(data) {
  const odin_order_id = localStorage.getItem('odin_order_id');
  const order_currency = localStorage.getItem('order_currency');

  return fetch('/paypal-verify-order', {
    credentials: 'same-origin',
    method: 'post',
    headers: {
      'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
      'content-type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      orderID: data.orderID
    })
  })
  .then(resp => {
    if (!resp.ok) {
      throw new Error(resp.statusText);
    }

    return resp.json();
  })
  .then(function(res) {
    goToThankYou(odin_order_id, order_currency);
  })
  .catch(err => {

  });
}
