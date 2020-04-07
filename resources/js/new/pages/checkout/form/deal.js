import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        deal: null,
      },
    };
  },


  created() {
    this.deal_init();
  },


  validations() {
    return {
      deal: {
        required,
      },
    };
  },


  watch: {

    'form.deal'(value) {
      window.selectedOffer = value ? 1 : 0;
      history.pushState({}, '', location.href);
      this.print_pixels('cart');
    },

  },


  computed: {

    deals_by_value() {
      return js_data.deal_available.reduce((acc, deal) => {
        acc[deal] = js_data.product.prices[deal];
        return acc;
      }, {});
    },

  },


  methods: {

    deal_init() {
      if (js_query_params.qty) {
        const deal = +js_query_params.qty;

        if (this.deals_by_value[deal]) {
          this.form.deal = deal;
        }
      }
    },

    deal_change(value) {
      this.form.deal = value;
    },

  },

};
