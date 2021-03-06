import { getCountOfInstallments } from './installments';

export const getRadioHtml = ({ discountName, newPrice, textComposite, price, discountText, installments, discountPercent, totalQuantity }) =>
  `<div class='main-row'>
    ${totalQuantity === 3 ? "<img class='lazy best-seller' data-src='" + js_data.cdn_url + "/assets/images/best-seller-checkout4.png' alt='best seller' />" : "" }
    <p class="product-name">
      <span class="product-text">${textComposite}</span> - <span class="discount">${discountText}</span>
    </p>
    <div class='prices'>
      ${discountPercent > 0 ? `
        <div class='new-price red'>${`${getCountOfInstallments(installments)}` + newPrice}</div>
        <div class="strike">${`${getCountOfInstallments(installments)}` + price}</div>
        ` : `
        <div class="original-price">${`${getCountOfInstallments(installments)}` + price}</div>
        `}
    </div>
  </div>`
