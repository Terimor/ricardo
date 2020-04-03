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
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => res.value_text)
    .catch(err => {

    });
};

export const getUppSells = (product_id, quantity, accessoryStep) => {
  const cur = localStorage.getItem('order_currency');

  return fetch(`/upsell-product/${product_id}/?quantity=${quantity}${cur ? '&cur=' + cur : ''}`)
    .then(resp => {
      if (!resp.ok) {
        window.serverData[accessoryStep] = {
          status: resp.status,
          statusText: resp.statusText,
        };

        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => ({ data: res }))
    .catch(err => {

    });
};
