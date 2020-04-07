import searchPopulate from '../utils/searchPopulate';
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

  let url = `/upsell-product/${product_id}/?quantity=${quantity}${cur ? '&cur=' + cur : ''}`;

  window.serverData[accessoryStep] = {
    success: false,
    error: false,
    url,
  };

/*
  return fetch(url, {
      method: 'get',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(resp => {
      window.serverData[accessoryStep] = {
        success: true,
        status: resp.status,
        statusText: resp.statusText,
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
        error: true,
        message: err.message,
        stack: err.stack,
      };
    });
*/

  return new Promise(resolve => {
    url = searchPopulate(url);

    js_deps.wait(['axios'], () => {
      axios({
          url,
          method: 'get',
          responseType: 'json',
        })
        .then(resp => {
          window.serverData[accessoryStep] = {
            url,
            success: true,
            status: resp.status,
            statusText: resp.statusText,
            headers: resp.headers,
            data: resp.data,
          };

          resolve(resp);
        })
        .catch(err => {
          if (err.response && err.response.data && err.response.data.trace) {
            delete err.response.data.trace;
          }

          window.serverData[accessoryStep] = {
            url,
            error: true,
            message: err.message || null,
            status: err.response && err.response.status || null,
            statusText: err.response && err.response.statusText || null,
            headers: err.response && err.response.headers || null,
            data: err.response && err.response.data || null,
          };
        });
    });
  });
};
