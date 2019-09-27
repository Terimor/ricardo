export const getOrderAmount = (orderId) => {
  const cur = localStorage.getItem('order_currency');
  return fetch(`/order-amount-total/${orderId}/${cur ? '?cur=' + cur : ''}`).then(res => res.json());
}
