let cache = {
  first_name: null,
  last_name: null,
  email: null,
  phone: null,
};


export default {

  methods: {

    check_for_leads_request() {
      const data = {
        email: this.form.email,
        first_name: this.form.first_name,
        last_name: this.form.last_name,
        sku: this.form.variant || js_data.product.skus[0].code,
        phone: this.form.phone
          ? this.form.phone_code + this.form.phone.replace(/[^0-9]/g, '')
          : null,
        page: location.href,
      };

      if (this.form.first_name && this.form.last_name && this.form.email) {
        const updated =
          data.first_name !== cache.first_name ||
          data.last_name !== cache.last_name ||
          data.email !== cache.email ||
          data.phone !== cache.phone;

        if (updated) {
          cache = {
            first_name: data.first_name,
            last_name: data.last_name,
            email: data.email,
            phone: data.phone,
          };

          Promise.resolve()
            .then(() => {
              return this.fingerprint_calculate();
            })
            .then(result => {
              data.f = result;
            })
            .then(() => {
              return this.fetch_post('/new-customer', data);
            })
            .catch(err => {

            });
        }
      }
    },

  },

};
