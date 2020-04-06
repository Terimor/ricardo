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

  const url = `/upsell-product/${product_id}/?quantity=${quantity}${cur ? '&cur=' + cur : ''}`;

  window.serverData[accessoryStep] = {
    stage: 0,
  };

  return fetch(url)
    .then(resp => {
      window.serverData[accessoryStep] = {
        stage: 1,
        url,
        status: resp.status,
        statusText: resp.statusText,
        body: resp.body instanceof ReadableStream
          ? 'ReadableStream'
          : resp.body,
      };

      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => {
      window.serverData[accessoryStep].bodyJson = res;
      return res;
    })
    .then(res => ({ data: res }))
    .catch(err => {
      window.serverData[accessoryStep] = {
        stage: 2,
        url,
        error: true,
        message: err.message,
        stack: err.stack,
      };
    });
};
