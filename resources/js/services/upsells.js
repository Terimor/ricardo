import { groupBy } from '../utils/groupBy';
import { queryParams } from '../utils/queryParams';

export const getTotalPrice = (data, total) => {
  const formattedData = groupBy(data, 'id', 'quantity')

  return axios
    .post(`/calculate-upsells-total`,
      {
        ...queryParams(),
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
        ...queryParams(),
      }
    })
    .then((res) => {
      return res;
    });
};
