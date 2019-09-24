import { groupBy } from '../utils/groupBy';

export const getTotalPrice = (data, total) => {
  const formattedData = groupBy(data, 'id', 'quantity')
  const cur = localStorage.getItem('order_currency');

  return axios
    .post(`/calculate-upsells-total/${cur ? '?cur=' + cur : ''}`,
      {
        upsells: formattedData,
        total,
      },
      {
        credentials: 'same-origin',
        headers: {
          accept:
          'application/json',
          'content-type': 'application/json'
        },
      })
    .then(({ data }) => {
      return data.value_text;
    });
};

export const getUppSells = (product_id, quantity) => {
  const cur = localStorage.getItem('order_currency');
  return axios
    .get(`/upsell-product/${product_id}/${cur ? '?cur=' + cur : ''}`, {
      params: {
        quantity,
      }
    })
    .then((res) => {
      return res;
    });
};
