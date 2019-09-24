export const getOrderAmount = (orderId) => {
  const cur = localStorage.getItem('order_currency');
  return axios
    .get(`/order-amount-total/${orderId}/${cur ? '?cur=' + cur : ''}`)
    .then((res) => {
      return res.data;
    });
}
