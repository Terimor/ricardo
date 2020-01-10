import input from '../../components/input';
import deal from './form/deal';
import variant from './form/variant';
import installments from './form/installments';
import first_name from './form/first_name';
import last_name from './form/last_name';
import email from './form/email';
import phone from './form/phone';
import zipcode from './form/zipcode';
import street from './form/street';
import building from './form/building';
import complement from './form/complement';
import district from './form/district';
import city from './form/city';
import state from './form/state';
import country from './form/country';
import payment_provider from './form/payment_provider';
import payment_method from './form/payment_method';
import warranty from './form/warranty';
import card_holder from './form/card_holder';
import card_type from './form/card_type';
import card_number from './form/card_number';
import card_date from './form/card_date';
import card_cvv from './form/card_cvv';
import document_type from './form/document_type';
import document_number from './form/document_number';
import terms from './form/terms';


export default {

  mixins: [
    input,
    deal,
    variant,
    installments,
    first_name,
    last_name,
    email,
    phone,
    zipcode,
    street,
    building,
    complement,
    district,
    city,
    state,
    country,
    payment_provider,
    payment_method,
    warranty,
    card_holder,
    card_type,
    card_number,
    card_date,
    card_cvv,
    document_type,
    document_number,
    terms,
  ],


  validations() {
    return {
      ...deal.validations.call(this),
      ...variant.validations.call(this),
      ...installments.validations.call(this),
      ...first_name.validations.call(this),
      ...last_name.validations.call(this),
      ...email.validations.call(this),
      ...phone.validations.call(this),
      ...zipcode.validations.call(this),
      ...street.validations.call(this),
      ...building.validations.call(this),
      ...complement.validations.call(this),
      ...district.validations.call(this),
      ...city.validations.call(this),
      ...state.validations.call(this),
      ...country.validations.call(this),
      ...payment_provider.validations.call(this),
      ...payment_method.validations.call(this),
      ...warranty.validations.call(this),
      ...card_holder.validations.call(this),
      ...card_type.validations.call(this),
      ...card_number.validations.call(this),
      ...card_date.validations.call(this),
      ...card_cvv.validations.call(this),
      ...document_type.validations.call(this),
      ...document_number.validations.call(this),
      ...terms.validations.call(this),
    };
  },


  data() {
    return {
      is_submitted: false,
      is_loading: {
        address: false,
      },
      extra_validation: {
        
      },
      payment_methods: js_data.payment_methods
        ? JSON.parse(JSON.stringify(js_data.payment_methods))
        : {},
      payment_error: null,
    };
  },


  created() {
    this.form_check_3ds_failure();
  },


  watch: {

    'form.country'() {
      this.payment_methods_reload();
    },

  },


  computed: {

    extra_fields() {
      const payment_method = Object.keys(this.payment_methods)
        .filter(name => name !== 'instant_transfer')
        .shift();

      return this.payment_methods[payment_method]
        ? this.payment_methods[payment_method].extra_fields || {}
        : {};
    },

  },


  methods: {

    payment_methods_reload() {
      return this.fetch_get('/payment-methods-by-country?country=' + this.form.country)
        .then(this.fetch_json)
        .then(body => {
          this.payment_methods = body;
        })
        .catch(err => {

        });
    },

    form_check_fields_valid(fields) {
      let is_valid = true;

      for (let name of fields) {
        if (this.$v.form[name]) {
          this.$v.form[name].$touch();

          if (this.$v.form[name].$invalid || this.$v.form[name].$pending) {
            is_valid = false;
          }
        }
      }

      return is_valid;
    },

    form_check_3ds_failure() {
      if (js_query_params['3ds'] === 'failure') {
        try {
          this.form = JSON.parse(localStorage.getItem('selectedProductData'));

          Promise.resolve()
            .then(() => {
              const order_id = localStorage.getItem('odin_order_id');
              return fetch_get('/pay-by-card-errors?order=' + order_id);
            })
            .then(this.fetch_json)
            .then(body => {
              this.payment_error = this.t('checkout.payment_error');

              if (body.errors && body.errors.length > 0) {
                this.payment_error = this.t(body.errors[0]);
              }
            })
            .catch(err => {
              this.payment_error = this.t('checkout.payment_error');
            });
        }
        catch (err) {
          
        }
      }
    },

  },

};
