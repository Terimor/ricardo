import { getCountOfInstallments } from './emc1';

export const preparePartByInstallments = (value, installment) => Number((value / installment).toFixed(2))

const getDiscount = ({key, price, installments}) => {
  const config = {
    1: '(50% Discount)',
    3: `(69% Discount, ${getCountOfInstallments(installments)}₴${preparePartByInstallments(price, installments).toLocaleString()}/Unit)`,
    5: `(73% Discount, ${getCountOfInstallments(installments)}₴${preparePartByInstallments(price, installments).toLocaleString()}/Unit)`,
  }

  return config[key]
}

export function preparePurchaseData({purchaseList, quantityToShow = [1, 3, 5], long_name, variant, installments}) {
  return  Object.keys(purchaseList)
    .filter((key) => quantityToShow.includes(+key))
    .map((key, idx) => {
      const it = purchaseList[key]
      const price = it.value * 2 * key
      const mainQuantity =
        +key === 3 ? 2 :
          +key === 5 ? 3 :
            +key

      const freeQuantity =
            +key === 3 ? 1 :
              +key === 5 ? 2 :
                null

      return  {
        discountName:
                    it.is_bestseller ? 'BESTSELLER' :
                      it.is_popular ? 'BEST DEAL' :
                        '',
        newPrice: preparePartByInstallments(it.value * mainQuantity, installments),
        withDiscount: idx > 0,
        text: `${mainQuantity}x ${long_name} ${variant} ${freeQuantity ? freeQuantity + ' FREE' : ''}`,
        price: preparePartByInstallments(price, installments),
        discountText: getDiscount({
          key,
          price,
          installments,
        }),
        totalQuantity: +key
      }
    })
}
