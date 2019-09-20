import { goTo } from './goTo';

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
    }
    return data.id;
  });
}

export function paypalOnApprove(data) {
  localStorage.setItem('order_id', data.orderID);
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
        localStorage.setItem('odin_order_created_at', new Date);
        goTo(`/thankyou/?order=${odin_order_id}`);
      }
    });
}
