export function bluesnapCreateOrder(data, bs_pf_token, currency, amount) {
  return new Promise((resolve, reject) => {
    bluesnap.threeDsPaymentsSetup(bs_pf_token, sdkResponse => {
      if (+sdkResponse.code !== 1) {
        return reject(sdkResponse.info.errors[0] || sdkResponse.info.warnings[0]);
      }

      resolve(sdkResponse.threeDSecure.threeDSecureReferenceId);
    });

    const card = {
      ccNumber: data.card.number.replace(/[^0-9]/g, ''),
      expDate: data.card.month + '/' + data.card.year,
      cvv: data.card.cvv.toString(),
      currency,
      amount,
    };

    bluesnap.threeDsPaymentsSubmitData(card);
  });
}
