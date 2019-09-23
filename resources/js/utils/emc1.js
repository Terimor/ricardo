import { getCountOfInstallments } from './installments';
import { goTo } from './goTo';

export const getRadioHtml = ({ discountName, newPrice, text, price, discountText, currency = '$', installments, idx }) =>
  `${discountName
    ? `<p class="label-container-radio__best-seller">
      <span>${discountName}</span><span>${`${getCountOfInstallments(installments)}` + newPrice.toLocaleString()}</span>
    </p>`
    : ''}
  ${idx === 1 ? '<img class="share" src="/images/share.png">' : ''}
  <p class="label-container-radio__name-price">
    <span>${text}</span>
    <span ${idx !== 0 && discountName ? 'class="strike"' : ''}>${`${getCountOfInstallments(installments)}` + (!discountName ? newPrice : price).toLocaleString()}</span>
  </p>
  <p class="label-container-radio__discount">${discountText}</p>`

export function * getNotice ({
  users,
  cities,
  usersActive,
  bestsellerText
}) {
  let index = 0
  const messageMap = {
    paypal: `
      <div class="recently-notice">
        <div class="recently-notice__left">
          <img src="/images/paypal232.png" alt="">
        </div>
        <div class="recently-notice__right">
          <p>0 Risk purchase! Buy now with PayPal and return the product for free...</p>
        </div>
      </div>
    `,
    usersActive: `
      <div class="recently-notice recently-notice_user-active">
        <div class="recently-notice__left">
          <i class="fa fa-user"></i>
        </div>
        <div class="recently-notice__right">
          <p>Currently <text class="red">${usersActive}</text> people are looking at this product</p>
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

  const keyQueue = [
    'recentlyBought',
    'recentlyBought',
    'usersActive',
    'paypal',
    'recentlyBought',
    'recentlyBought',
    'bestsellerText'
  ]

  let lastIndex = 0

  while (true) {
    if (lastIndex < keyQueue.length - 1) {
      if (keyQueue[lastIndex] === 'recentlyBought') {
        if (users.length === index) {
          index = 0;
        }

        yield `<div class="recently-notice">
          <div class="recently-notice__left">
            <img src="${checkoutData.productImage}" alt="">
          </div>
          <div class="recently-notice__right">
            <p>${users[index]} ${cities[index] ? 'in ' + cities[index] : ''} just bought<br>1x ${checkoutData.product.product_name}</p>
          </div>
        </div>
      `
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

export function paypalCreateOrder ({
  xsrfToken = document.head.querySelector('meta[name="csrf-token"]').content,
  sku_code,
  sku_quantity,
  is_warranty_checked,
  order = '',
  page_checkout = document.location.href,
  cur,
  offer = new URL(document.location.href).searchParams.get('offer'),
  affiliate = new URL(document.location.href).searchParams.get('affiliate'),
}) {
  return fetch('/paypal-create-order', {
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
      order,
      page_checkout,
      cur,
      offer,
      affiliate,
    })
  }).then(function(res) {
    return res.json();
  }).then(function(data) {
    if (data.odin_order_id) {
      localStorage.setItem('odin_order_id', data.odin_order_id);
    }
    return data.id;
  });
}

export function paypalOnApprove(data) {
  localStorage.setItem('order_id', data.orderID);
  const odin_order_id = localStorage.getItem('odin_order_id');
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
  }).then(function(res) {
    if(res.ok) {
      return res.json();
    } else {
      throw new Error(res.statusText);
    }
  })
    .then(function(res) {
      if (odin_order_id) {
        localStorage.setItem('odin_order_created_at', new Date);
        goTo(`/thankyou-promos/?order=${odin_order_id}`);
      }
    });
}
