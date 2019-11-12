export const getCountOfInstallments = (installments) => installments && +installments !== 1 ? installments + '× ' : ''
