export const preparePartByInstallments = (value, installment) => Number((value / installment).toFixed(2));

export const getCountOfInstallments = (installments) => installments && +installments !== 1 ? installments + '× ' : ''
