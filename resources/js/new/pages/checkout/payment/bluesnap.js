export default {

  methods: {

    bluesnap_create_order(bs_pf_token, currency, amount) {
      return new Promise((resolve, reject) => {
        bluesnap.threeDsPaymentsSetup(bs_pf_token, sdkResponse => {
          if (+sdkResponse.code !== 1) {
            return reject(sdkResponse.info.errors[0] || sdkResponse.info.warnings[0]);
          }

          resolve(sdkResponse.threeDSecure.threeDSecureReferenceId);
        });

        const card = {
          ccNumber: this.form.card_number.replace(/[^0-9]/g, ''),
          expDate: this.form.card_date.substr(0, 3) + '20' + this.form.card_date.substr(3),
          cvv: this.form.card_cvv.toString(),
          currency,
          amount,
        };

        bluesnap.threeDsPaymentsSubmitData(card);
      });
    },

  },

};
