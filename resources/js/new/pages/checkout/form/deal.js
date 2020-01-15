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
    },
  },


  computed: {

    deals_by_value() {
      let deals_by_value = {};

      for (let deal of js_data.deals) {
        deals_by_value[deal.quantity] = deal;
      }

      return deals_by_value;
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
