export const getTotalPrice = (data, total) => {
  return axios
    .post(`/calculate-upsells-total`,
      {
        upsells: data,
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
    .get(`/upsell-product/${product_id}?quantity=${quantity}`)
    .then((res) => {
      return res;
    });
};

export const getOrderAmount = (orderId) => {
  return axios
    .get(`/order-amount-total/${orderId}`)
    .then((res) => {
      return res;
    });
}
