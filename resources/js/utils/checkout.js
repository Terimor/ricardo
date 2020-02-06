import fingerprint from '../services/fingerprintjs2';
import { bluesnapCreateOrder } from '../services/bluesnap';
import { getCountOfInstallments } from './installments';
import { t } from './i18n';
import { queryParams } from  './queryParams';
import { goTo } from './goTo';


const getDiscount = ({key, discountPercent, valueTexts, installments}) => {

  const currentPrice = queryParams().tpl === 'emc1b'
    ? `${getCountOfInstallments(installments)}${valueTexts.valueText[installments]}`
    : `${getCountOfInstallments(installments)}${valueTexts.unitValueText[installments]}/${t('checkout.unit')}`;

  const config = {
    1: `(${discountPercent}% ${t('checkout.discount')})`,
    2: `(${discountPercent}% ${t('checkout.discount')}, ${currentPrice})`,
    3: `(${discountPercent}% ${t('checkout.discount')}, ${currentPrice})`,
    4: `(${discountPercent}% ${t('checkout.discount')}, ${currentPrice})`,
    5: `(${discountPercent}% ${t('checkout.discount')}, ${currentPrice})`,
  }

  return config[key]
}

const getNewPrice = ({key, valueTexts, installments}) => {
  const config = {
    1: `${valueTexts.valueText[installments]}`,
    2: `${valueTexts.valueText[installments]}`,
    3: `${valueTexts.valueText[installments]}`,
    4: `${valueTexts.valueText[installments]}`,
    5: `${valueTexts.valueText[installments]}`,
  }

  return config[key]
}

const getOldPrice = ({key, valueTexts, installments}) => {
  const config = {
    1: `${valueTexts.oldValueText[installments]}`,
    2: `${valueTexts.oldValueText[installments]}`,
    3: `${valueTexts.oldValueText[installments]}`,
    4: `${valueTexts.oldValueText[installments]}`,
    5: `${valueTexts.oldValueText[installments]}`,
  }

  return config[key]
};

const getOnlyDiscount = ({key, discountPercent}) => {
  const config = {
    1: `(${discountPercent}% ${t('checkout.discount')})`,
    2: `(${discountPercent}% ${t('checkout.discount')})`,
    3: `(${discountPercent}% ${t('checkout.discount')})`,
    4: `(${discountPercent}% ${t('checkout.discount')})`,
    5: `(${discountPercent}% ${t('checkout.discount')})`
  };

  return config[key]
};

export function preparePurchaseData({
  purchaseList,
  quantityToShow = [1, 3, 5],
  product_name,
  variant,
  installments,
  customOrder=false,
  onlyDiscount=false,
  image,
  isOutOfStock = false
}) {
  const data = Object.keys(purchaseList)
    .filter((key) => quantityToShow.indexOf(+key) !== -1)
    .map((key, idx) => {
      const it = purchaseList[key];
      const discountPercent = it.discount_percent;

      let valueTexts = {
        valueText: {
          1: it.value_text,
          3: it.installments3_value_text,
          6: it.installments6_value_text
        },
        oldValueText: {
          1: it.old_value_text,
          3: it.installments3_old_value_text,
          6: it.installments6_old_value_text
        },
        unitValueText: {
          1: it.unit_value_text,
          3: it.installments3_unit_value_text,
          6: it.installments6_unit_value_text
        },
      }

      const price = it.value_text;
      const mainQuantity =
        +key === 3 ? 2 :
          +key === 5 ? 3 :
            +key;

      const freeQuantity =
        +key === 3 ? 1 :
          +key === 5 ? 2 :
            null;

      const isTextComposite = (amount) => {
        return amount ? ` + ${amount} ${t('checkout.free')}` : '';
      };

      const isSellOutArray = queryParams().sellout
        ? queryParams().sellout.split(',')
        : [];

      return  {
        discountPercent,
        image: it.image || image,
        discountName:
          it.is_bestseller ? t('checkout.bestseller') :
            it.is_popular ? t('checkout.best_deal') :
              '',
        text: `${mainQuantity + freeQuantity}x ${product_name}`,
        textComposite: `${mainQuantity}x ${product_name}${isTextComposite(freeQuantity)}`,
        newPrice: getNewPrice({
          key,
          valueTexts,
          installments,
        }),
        price: getOldPrice({
          key,
          valueTexts,
          installments,
        }),
        pricePerUnit: valueTexts.unitValueText,
        discountText: onlyDiscount ?
          getOnlyDiscount({key, discountPercent}) :
          getDiscount({
            key,
            discountPercent,
            valueTexts,
            installments,
          }),
        totalQuantity: +key,
        isOutOfStock: isSellOutArray.indexOf(String(idx + 1)) !== -1,
        isBestseller: it.is_bestseller,
        isPopular: it.is_popular
      }
    });
  if(customOrder) {
    [data[0],data[1],data[2],data[3],data[4]] = [data[4],data[2],data[0],data[1],data[3]];
  }
  return data
}

export function getCardUrl(cardType) {
  const cardMap = {
    'american-express': js_data.cdn_url + '/assets/images/cc-icons/american-express.png',
    'aura': js_data.cdn_url + '/assets/images/cc-icons/aura.png',
    'diners-club': js_data.cdn_url + '/assets/images/cc-icons/diners-club.png',
    'discover': js_data.cdn_url + '/assets/images/cc-icons/discover.png',
    'elo': js_data.cdn_url + '/assets/images/cc-icons/elo.png',
    'hipercard': js_data.cdn_url + '/assets/images/cc-icons/hipercard.png',
    'iconcc': js_data.cdn_url + '/assets/images/cc-icons/iconcc.png',
    'jcb': js_data.cdn_url + '/assets/images/cc-icons/jcb.png',
    'maestro': js_data.cdn_url + '/assets/images/cc-icons/maestro.png',
    'mastercard': js_data.cdn_url + '/assets/images/cc-icons/mastercard.png',
    'visa': js_data.cdn_url + '/assets/images/cc-icons/visa.png'
  }
  return cardMap[cardType] || cardMap.iconcc
}


export function sendCheckoutRequest(data) {
  localStorage.setItem('3ds_params', JSON.stringify(js_query_params));

  let url_search = '?cur=' + (!js_query_params.cur || js_query_params.cur === '{aff_currency}'
    ? js_data.product.prices.currency
    : js_query_params.cur);

  if (localStorage.getItem('order_failed')) {
    url_search += '&order=' + localStorage.getItem('odin_order_id');
  }

  data.page_checkout = location.href;

  return Promise.resolve()
    .then(fingerprint)
    .then(hash => data.f = hash)
    .then(() => fetch('/pay-by-card' + url_search, {
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
      if (res.bs_pf_token) {
        localStorage.setItem('odin_order_id', res.order_id);
        localStorage.setItem('order_failed', res.order_id);

        return bluesnapCreateOrder(data, res.bs_pf_token, res.order_currency, res.order_amount)
          .then(bs_3ds_ref => {
            data.card.bs_3ds_ref = bs_3ds_ref;
            sendCheckoutRequest(data);
            return new Promise(resolve => {});
          })
          .catch(errText => {
            return Promise.reject({ errText });
          });
      }

      return res;
    })
    .then(res => {
      if (res.order_id) {
        localStorage.setItem('odin_order_id', res.order_id);
        localStorage.setItem('order_currency', res.order_currency);
        localStorage.setItem('order_number', res.order_number);
        localStorage.setItem('order_id', res.id);

        if (res.status === 'ok') {
          localStorage.removeItem('order_failed');
        } else {
          localStorage.setItem('order_failed', res.order_id);
        }
      }

      if (res.status !== 'ok') {
        res.paymentError = t('checkout.payment_error');

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

        if (res.error) {
          if (res.error.phrase) {
            res.paymentError = t(res.error.phrase);
          } else if (res.error.message) {
            res.paymentError = res.error.message;
          }
        }
      }

      if (res.status === 'ok') {
        if (res.redirect_url) {
          location.href = res.redirect_url;
        } else {
          goToThankYou(res.order_id, res.order_currency);
        }
      }

      return res;
    })
    .catch(err => {
      return {
        paymentError: err && err.errText || t('checkout.payment_error'),
      };
    });
}


export function goToThankYou(order, cur) {
  const url_pathname = js_data.product.upsells.length > 0
    ? '/thankyou-promos'
    : '/thankyou';

  let url_search = '?order=' + order + '&cur=' + cur;

  if (js_query_params.cop_id) {
    url_search += '&cop_id=' + encodeURIComponent(js_query_params.cop_id);
  }

  localStorage.setItem('odin_order_created_at', new Date());

  goTo(url_pathname + url_search, ['3ds', '3ds_restore']);
}


export function get3dsErrors() {
  const order_id = localStorage.getItem('odin_order_id');

  return Promise.resolve()
    .then(() => fetch('/pay-by-card-errors?order=' + order_id, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    }))
    .then(resp => {
      if (!resp.ok) {
        throw new Error(resp.statusText);
      }

      return resp.json();
    })
    .then(res => {
      let paymentError = t('checkout.payment_error');

      if (res.errors && res.errors.length > 0) {
        paymentError = t(res.errors[0]);
      }

      return paymentError;
    })
    .catch(err => {
      return t('checkout.payment_error');
    });
}


export function applyMaskForInput(value, mask, schema) {
  let counter = 0;

  value = value || '';

  function traverse() {
    for (let i = 0; i < value.length; i++) {
      const regexp = new RegExp(schema[i] || '.');
      const symbol = mask.substr(i, 1) || '';

      if (!symbol) {
        value = value.substr(0, i);
        return;
      }

      if (symbol === 'x' && !regexp.test(value[i])) {
        value = value.substr(0, i) + value.substr(i + 1);

        if (counter++ < 100) {
          traverse();
        }

        return;
      }

      if (symbol !== 'x' && value[i] !== symbol) {
        value = value.substr(0, i) + symbol + value.substr(i);

        if (counter++ < 100) {
          traverse();
        }

        return;
      }
    }
  }

  traverse();

  return value;
}
