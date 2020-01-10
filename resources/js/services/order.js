export const getOrderAmount = (orderId) => {
  const cur = localStorage.getItem('order_currency') || 'USD';

  return fetch(`/order-amount-total/${orderId}/${cur ? '?cur=' + cur : ''}`)
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .catch(err => {

    });
}
