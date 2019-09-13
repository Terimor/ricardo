
export const getOrderAmount = (orderId) => {
  return axios
    .get(`/order-amount-total/${orderId}`)
    .then((res) => {
      return res;
    });
}
