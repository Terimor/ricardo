import { getCountOfInstallments } from './emc1';

export const preparePartByInstallments = (value, installment) => Number((value / installment).toFixed(2))

const getDiscount = ({key, discountPercent, valueTexts, installments}) => {
  const config = {
    1: `(${discountPercent}% Discount)`,
    3: `(${discountPercent}% Discount, ${getCountOfInstallments(installments)}${valueTexts.unitValueText[installments]}/Unit)`,
    5: `(${discountPercent}% Discount, ${getCountOfInstallments(installments)}${valueTexts.unitValueText[installments]}/Unit)`,
  }

  return config[key]
}

const getNewPrice = ({key, valueTexts, installments}) => {
  const config = {
    1: `${valueTexts.valueText[installments]}`,
    3: `${valueTexts.valueText[installments]}`,
    5: `${valueTexts.valueText[installments]}`,
  }

  return config[key]
}

const getOldPrice = ({key, valueTexts, installments}) => {
  const config = {
    1: `${valueTexts.oldValueText[installments]}`,
    3: `${valueTexts.oldValueText[installments]}`,
    5: `${valueTexts.oldValueText[installments]}`,
  }

  return config[key]
}

export function preparePurchaseData({purchaseList, quantityToShow = [1, 3, 5], long_name, variant, installments, customOrder=false}) {
  let data = Object.keys(purchaseList)
    .filter((key) => quantityToShow.includes(+key))
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
        }
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

      return  {
        discountName:
          it.is_bestseller ? 'BESTSELLER' :
            it.is_popular ? 'BEST DEAL' :
              '',
        withDiscount: idx > 0,
        text: `${mainQuantity}x ${long_name} ${freeQuantity ? ' + ' + freeQuantity + ' FREE' : ''}`,
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
        discountText: getDiscount({
          key,
          discountPercent,
          valueTexts,
          installments,
        }),
        totalQuantity: +key
      }
    });
  if(customOrder) {
    [data[0],data[1],data[2],data[3],data[4]] = [data[4],data[2],data[0],data[1],data[3]];
  }
  return data
}

export function getCardUrl(cardType) {
  const cardMap = {
    'american-express': '/images/cc-icons/american-express.png',
    'aura': '/images/cc-icons/aura.png',
    'diners-club': '/images/cc-icons/diners-club.png',
    'discover': '/images/cc-icons/discover.png',
    'elo': '/images/cc-icons/elo.png',
    'hipercard': '/images/cc-icons/hipercard.png',
    'iconcc': '/images/cc-icons/iconcc.png',
    'jcb': '/images/cc-icons/jcb.png',
    'maestro': '/images/cc-icons/maestro.png',
    'mastercard': '/images/cc-icons/mastercard.png',
    'visa': '/images/cc-icons/visa.png'
  }
  return cardMap[cardType] || cardMap.iconcc
}
