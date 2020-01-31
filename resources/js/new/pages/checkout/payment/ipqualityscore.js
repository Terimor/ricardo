export default {

  methods: {

    ipqualityscore_form_calculate() {
      let fields = {
        order_quantity: this.form.deal,
        order_amount: this.price_total_value_usd,
      };

      if (this.form.first_name) {
        fields.billing_first_name = this.form.first_name;
      }

      if (this.form.last_name) {
        fields.billing_last_name = this.form.last_name;
      }

      if (this.form.country) {
        fields.billing_country = this.form.country;
      }

      if (this.form.street) {
        fields.billing_address_1 = this.form.street;
      }

      if (this.form.city) {
        fields.billing_city = this.form.city;
      }

      if (this.extra_fields.state) {
        fields.billing_region = this.form.state;
      }

      if (this.form.zipcode) {
        fields.billing_postcode = this.form.zipcode;
      }

      if (this.form.email) {
        fields.billing_email = this.form.email;
      }

      if (this.form.phone) {
        fields.billing_phone = this.form.phone_code + this.form.phone.replace(/[^0-9]/g, '');
      }

      if (this.form.card_number) {
        fields.credit_card_bin = this.form.card_number.replace(/[^0-9]/g, '').substr(0, 6);

        if (window.sha256) {
          fields.credit_card_hash = sha256(this.form.card_number.replace(/[^0-9]/g, ''));
        }
      }

      if (this.form.card_date) {
        fields.credit_card_expiration_month = this.form.card_date.split('/')[0];
        fields.credit_card_expiration_year = this.form.card_date.split('/')[1];
      }

      if (this.form.card_cvv) {
        fields.cvv_code = this.form.card_cvv;
      }

      return this.ipqualityscore_calculate(fields);
    },

  },

};
