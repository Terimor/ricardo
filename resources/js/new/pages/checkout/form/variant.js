import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        variant: null,
      },
      variant_opened: false,
    };
  },


  created() {
    this.variant_init();
  },


  validations() {
    return {
      variant: {
        required,
      },
    };
  },


  computed: {

    variants_by_code() {
      return js_data.product.skus.reduce((acc, sku) => {
        acc[sku.code] = sku;
        return acc;
      }, {});
    },

  },


  methods: {

    variant_init() {
      this.form.variant = js_data.product.skus[0]
        ? js_data.product.skus[0].code
        : null;
    },


    variant_toggle() {
      this.variant_opened = !this.variant_opened;
    },

    variant_change(value) {
      this.form.variant = value;
      this.variant_opened = false;
    },

  },

};
