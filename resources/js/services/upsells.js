export const getTotalPrice = (data, total) => {
  return axios
    .post(`${window.location.origin}/calculate-upsells-total`,
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
    .get(`${window.location.origin}/upsell-product/${product_id}?quantity=${quantity}`)
    .then((res) => {
      return res
    });
};

export const getOrderAmount = (orderId) => {
  return axios
    .get(`${window.location.origin}/order-amount-total/${orderId}`)
    .then((res) => {
      return res
    });
}
