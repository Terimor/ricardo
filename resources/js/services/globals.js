import * as cookies from '../utils/cookies';


if (location.pathname === '/checkout') {
  const product_id = cookies.getCookie('product_id');

  if (product_id !== checkoutData.product.id) {
    localStorage.clear();
    cookies.clearCookies({ except: ['XSRF-TOKEN'] });
    cookies.setCookie('product_id', checkoutData.product.id);
  }
}
