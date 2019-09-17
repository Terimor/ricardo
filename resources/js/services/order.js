import { queryParams } from '../utils/queryParams';

export const getOrderAmount = (orderId) => {
  return axios
    .get(`/order-amount-total/${orderId}`, {
      params: {
        ...queryParams(),
      }
    })
    .then((res) => {
      return res.data;
    });
}
