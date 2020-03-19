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
        page: location.href,
      };

      if (!this.$v.form.phone.$invalid) {
        data.phone = this.form.phone
          ? this.form.phone_code + this.form.phone.replace(/[^0-9]/g, '')
          : null;
      }

      if (this.form.first_name && this.form.last_name && this.form.email) {
        const updated =
          data.first_name !== cache.first_name ||
          data.last_name !== cache.last_name ||
          data.email !== cache.email ||
          (!this.$v.form.phone.$invalid && data.phone !== cache.phone);

        if (updated) {
          const new_value = {
            first_name: data.first_name,
            last_name: data.last_name,
            email: data.email,
            phone: data.phone,
          };

          cache = new_value;

          setTimeout(() => {
            if (cache === new_value) {
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
          }, 1000);
        }
      }
    },

  },

};
