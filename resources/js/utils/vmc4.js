export const getCountOfInstallments = (installments) => installments && installments !== 1 ? installments + '× ' : ''

export const getRadioHtml = ({ discountName, newPrice, text, price, discountText, installments, idx }) =>
  `<div class='main-row'>
    ${discountName.toLowerCase() === 'bestseller' ? "<img class='best-seller' src='/images/best-seller-checkout4.png' alt='best seller' />" : "" }
    <p class="product-name">
      <span class="product-text">${text}</span><span class="discount">${discountText}</span>
    </p>
    <div class='prices'>
      <div class='new-price red'>${`${getCountOfInstallments(installments)}` + newPrice}</div>
      <div ${idx !== 0 && discountName ? 'class="strike"' : ''}>${`${getCountOfInstallments(installments)}` + (!discountName ? newPrice : price)}</div>
    </div>
  </div>`
