import { getCountOfInstallments } from './installments';

export const getRadioHtml = ({ discountName, newPrice, text, textComposite, price, discountText, installments, discountPercent }) =>
  `<div class='main-row'>
    ${discountName.toLowerCase() === 'bestseller' ? "<img class='best-seller' src='/images/best-seller-checkout4.png' alt='best seller' />" : "" }
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
