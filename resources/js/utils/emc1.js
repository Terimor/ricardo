import { getRandomInt } from './common';

export const getCountOfInstallments = (installments) => installments && installments !== 1 ? installments + 'X ' : ''

export const getRadioHtml = ({ discountName, newPrice, text, price, discountText, currency = '$', installments, idx }) =>
    `${discountName
        ? `<p class="label-container-radio__best-seller">
      <span>${discountName}</span><span>${`${getCountOfInstallments(installments)}` + currency + newPrice.toLocaleString()}</span>
    </p>`
        : ''}
  ${idx === 1 ? '<img class="share" src="/images/share.png">' : ''}
  <p class="label-container-radio__name-price">
    <span>${text}</span>
    <span ${idx !== 0 && discountName ? 'class="strike"' : ''}>${`${getCountOfInstallments(installments)}` + currency + (!discountName ? newPrice : price).toLocaleString()}</span>
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
