import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        variant: null,
      },
      variant_opened: false,
      variant_up: false,
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

    variant_name() {
      return this.variants_by_code[this.form.variant].name;
    },

    variant_image() {
      return this.variants_by_code[this.form.variant].quantity_image[1];
    },

    variants_by_code() {
      return js_data.product.skus.reduce((acc, sku) => {
        acc[sku.code] = sku;
        return acc;
      }, {});
    },

  },


  methods: {

    variant_init() {
      this.form.variant = !js_data.product.is_choice_required
        ? js_data.product.skus[0] && js_data.product.skus[0].code || null
        : null;
    },


    variant_toggle() {
      if (!this.variant_opened) {
        const item_height = 80;
        const input_rect = this.$refs.variant_field_input.getBoundingClientRect();
        const free_space = document.documentElement.clientHeight - (input_rect.top + input_rect.height);
        this.variant_up = free_space < item_height * js_data.product.skus.length;
      }

      this.variant_opened = !this.variant_opened;
    },

    variant_change(value) {
      this.form.variant = value;
      this.variant_opened = false;
    },

  },

};
