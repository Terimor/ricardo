import { getRandomInt } from './common';

export const getRadioHtml = ({ discountName, newPrice, text, price, discountText, currency = '$', installments }) =>
    `${discountName
        ? `<p class="label-container-radio__best-seller">
      <span>${discountName}</span><span>${`${installments && installments !== 1 ? installments + 'X ' : ''}` + currency + newPrice.toLocaleString()}</span>
    </p>`
        : ''}
  <p class="label-container-radio__name-price">
    <span>${text}</span>
    <span ${newPrice ? 'class="strike"' : ''}>${`${installments && installments !== 1 ? installments + 'X ' : ''}` + currency + price.toLocaleString()}</span>
  </p>
  <p class="label-container-radio__discount">${discountText}</p>`

export function * getNotice (productName) {
    const messageMap = {
        recentlyBought: `
      <div class="recently-notice">
        <div class="recently-notice__left">
          <img src="/images/headphones-white.png" alt="">
        </div>
        <div class="recently-notice__right">
          <p>Olivier B . in Tallinn just bought<br>1x ${productName}</p>
        </div>
      </div>
    `,
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
          <p>Currently <text class="red">${getRandomInt(33, 44)}</text> people are looking at this product</p>
        </div>
      </div>
    `,
        bestsellerText: `
      <div class="recently-notice recently-notice_high-demand">
        <div class="recently-notice__left">
          <i class="fa fa-user"></i>
        </div>
        <div class="recently-notice__right">
          <p>In high demand - This product is our bestseller right now...</p>
        </div>
      </div>
    `,
        paypalNotice: `<b>paypalNotice</b>`
    }

    const keyQueue = ['recentlyBought', 'recentlyBought', 'usersActive', 'paypal', 'recentlyBought', 'recentlyBought', 'bestsellerText']

    let lastIndex = 0

    while (true) {
        if (lastIndex < keyQueue.length - 1) {
            yield messageMap[keyQueue[lastIndex]]
            lastIndex++
        } else {
            yield messageMap[keyQueue[lastIndex]]
            lastIndex = 0
        }
    }
}
