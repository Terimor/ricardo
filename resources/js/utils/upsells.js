import { goTo } from './goTo';
import { t } from './i18n';

export function paypalCreateOrder ({
  xsrfToken = document.head.querySelector('meta[name="csrf-token"]').content,
  sku_code,
  sku_quantity,
  is_warranty_checked,
  order = '',
  page_checkout = document.location.href,
  offer = new URL(document.location.href).searchParams.get('offer'),
  affiliate = new URL(document.location.href).searchParams.get('affiliate'),
  upsells
}) {
  return fetch('/paypal-create-order', {
    method: 'post',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': xsrfToken,
      'content-type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
      sku_code,
      sku_quantity,
      is_warranty_checked,
      order,
      page_checkout,
      offer,
      affiliate,
      upsells
    })
  }).then(function(res) {
    return res.json();
  }).then(function(data) {
    if (data.odin_order_id) {
      localStorage.setItem('odin_order_id', data.odin_order_id);
      localStorage.setItem('order_currency', data.order_currency);
      localStorage.setItem('order_number', data.order_number);
      localStorage.setItem('order_id', data.id);
    }

    return data.id;
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
    .then(function(res) {
      if(res.ok) {
        return res.json();
      } else {
        throw new Error(res.statusText);
      }
    })
    .then(function() {
      if (odin_order_id) {
        localStorage.setItem('odin_order_created_at', new Date());
        goTo('/thankyou');
      }
    });
}


export function send1ClickRequest(data, upsells) {
  return Promise.resolve()
    .then(() => fetch('/pay-by-card-upsells', {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    }))
    .then(res => res.json())
    .then(res => {
      if (res.status !== 'ok') {
        res.paymentError = t('upsells.step_3.payment_error');

        if (res.errors && Object.keys(res.errors).length > 0) {
          res.paymentError = Object.values(res.errors).shift().shift();

          for (const name of Object.keys(data)) {
            res.paymentError = res.paymentError.replace(new RegExp(name + '.([0-9]+\.)?'), '');
          }
        }
      } else {
        if (res.upsells.reduce((value, upsell) => upsell.status !== 'ok', false)) {
          res.paymentError = '';

          if (res.upsells) {
            for (const upsell of res.upsells) {
              if (upsells.status !== 'ok') {
                res.paymentError += '<div>' + t('upsells.step_3.payment_error_one', {
                  upsell: upsells.filter(ups => ups.id === upsell.id).shift().name,
                }) + '</div>';
              }
            }
          }
        } else {
          if (res.order_id) {
            localStorage.setItem('odin_order_id', res.order_id);
            localStorage.setItem('order_currency', res.order_currency);
            localStorage.setItem('order_number', res.order_number);
            localStorage.setItem('order_id', res.id);
          }

          goToThankYou();
        }
      }

      return res;
    });
}


export function goToThankYou() {
  const odin_order_id = localStorage.getItem('odin_order_id');
  const order_currency = localStorage.getItem('order_currency');

  if (odin_order_id) {
    localStorage.setItem('odin_order_created_at', new Date());
    goTo('/thankyou?order=' + odin_order_id + '&cur=' + order_currency);
  }
}
