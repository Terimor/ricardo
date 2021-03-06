import fingerprint from '../services/fingerprintjs2';
import { goTo } from './goTo';
import { t } from './i18n';

export function paypalCreateOrder ({
  xsrfToken = document.head.querySelector('meta[name="csrf-token"]').content,
  sku_code,
  sku_quantity,
  is_warranty_checked,
  order = '',
  page_checkout = location.href,
  offer = js_query_params.offer || null,
  affiliate = js_query_params.affiliate || null,
  upsells
}) {
  let f = null;

  return Promise.resolve()
    .then(fingerprint)
    .then(hash => f = hash)
    .then(() => fetch('/paypal-create-order', {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': xsrfToken,
        'content-type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        is_upsell: true,
        sku_code,
        sku_quantity,
        is_warranty_checked,
        order,
        page_checkout,
        offer,
        affiliate,
        upsells,
        f,
      })
    }))
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => {
      if (res.odin_order_id) {
        localStorage.setItem('odin_order_id', res.odin_order_id);
        localStorage.setItem('order_currency', res.order_currency);
        localStorage.setItem('order_number', res.order_number);
        localStorage.setItem('order_id', res.id);
      }

      return res;
    })
    .catch(err => {

    });
}

export function paypalOnApprove(data) {
  const odin_order_id = localStorage.getItem('odin_order_id');

  return fetch('/paypal-verify-order', {
    credentials: 'same-origin',
    method: 'post',
    headers: {
      'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
      'content-type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      orderID: data.orderID
    })
  })
  .then(resp => {
    if (!resp.ok) {
      throw new Error(resp.statusText);
    }

    return resp.json();
  })
  .then(function() {
    if (odin_order_id) {
      localStorage.setItem('odin_order_created_at', new Date());
      goTo('/thankyou');
    }
  })
  .catch(err => {

  });
}


export function send1ClickRequest(data, upsells, paymentProvider) {
  data.method = paymentProvider;
  data.page_checkout = location.href;

  let url_payment = paymentProvider === 'credit-card'
    ? '/pay-by-card-upsells'
    : '/pay-by-apm-upsells';

  url_payment += '?cur=' + (!js_query_params.cur || js_query_params.cur === '{aff_currency}'
    ? js_data.product.prices.currency
    : js_query_params.cur);

  return Promise.resolve()
    .then(fingerprint)
    .then(hash => data.f = hash)
    .then(() => fetch(url_payment, {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    }))
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => {
      if (res.order_id) {
        localStorage.setItem('odin_order_id', res.order_id);
        localStorage.setItem('order_currency', res.order_currency);
        localStorage.setItem('order_number', res.order_number);
        localStorage.setItem('order_id', res.id);
      }
/*
      if (res.status !== 'ok') {
        res.paymentError = t('upsells.step_3.payment_error');

        if (res.errors) {
          if (Array.isArray(res.errors)) {
            if (res.errors.length > 0) {
              res.paymentError = t(res.errors[0]);
            }
          } else {
            if (Object.keys(res.errors).length > 0) {
              res.paymentError = res.message || Object.values(res.errors)[0][0];
            }
          }
        }
      }

      if (res.status === 'ok' && res.upsells.reduce((value, upsell) => upsell.status !== 'ok', false)) {
        res.paymentError = '';

        for (let upsell of res.upsells) {
          if (upsells.status !== 'ok') {
            res.paymentError += '<div>' + t('upsells.step_3.payment_error_one', {
              product: upsells.filter(ups => ups.id === upsell.id).shift().name,
            }) + '</div>';
          }
        }
      }
*/
      const odin_order_id = res.order_id || localStorage.getItem('odin_order_id');
      const order_currency = res.order_currency || localStorage.getItem('order_currency');
      localStorage.setItem('odin_order_created_at', new Date());

      if (res.redirect_url) {
        location.href = res.redirect_url;
      } else {
        goTo('/thankyou?order=' + odin_order_id + '&cur=' + order_currency);
      }

      return res;
    })
    .catch(err => {
      return {
        paymentError: t('upsells.step_3.payment_error'),
      };
    });
}
