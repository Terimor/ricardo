import { groupBy } from '../utils/groupBy';

export const getTotalPrice = (data, total) => {
  const formattedData = groupBy(data, 'id', 'quantity')

  return axios
    .post(`/calculate-upsells-total`,
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
  return axios
    .get(`/upsell-product/${product_id}`, {
      params: {
        quantity,
      }
    })
    .then((res) => {
      return res;
    });
};
