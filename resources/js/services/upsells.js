import { groupBy } from '../utils/groupBy';

export const getTotalPrice = (data, total) => {
  const formattedData = groupBy(data, 'id', 'quantity')
  const cur = localStorage.getItem('order_currency');

  return fetch(`/calculate-upsells-total/${cur ? '?cur=' + cur : ''}`, {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
        'accept': 'application/json',
        'content-type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        upsells: formattedData,
        total,
      }),
    })
    .then(res => res.json())
    .then(res => res.value_text);
};

export const getUppSells = (product_id, quantity) => {
  const cur = localStorage.getItem('order_currency');

  return fetch(`/upsell-product/${product_id}/?quantity=${quantity}${cur ? '&cur=' + cur : ''}`)
    .then(res => res.json())
    .then(res => ({ data: res }));
};
