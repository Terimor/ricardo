import { required } from 'vuelidate/lib/validators';


export default {

  data() {
    return {
      form: {
        terms: false,
      },
    };
  },


  validations() {
    return {
      terms: {
        required,
        valid(value) {
          return value === true;
        },
      },
    };
  },


  methods: {

    terms_init() {
      js_deps.wait_for(
        () => {
          return !!this.$refs.terms_field
            && !!this.$refs.terms_field.querySelector('.terms-field-label');
        },
        () => {
          setTimeout(() => {
            const element = this.$refs.terms_field.querySelector('.terms-field-label');

            if (element.terms_init) {
              return;
            }

            element.terms_init = true;

            const links = element.querySelectorAll('a');

            for (let i = 0; i < links.length; i++) {
              links[i].addEventListener('click', event => event.stopPropagation());
            }
          }, 100);
        },
      );
    },
 
    terms_change(value) {
      const element = this.$refs.terms_field.querySelector('.terms-field-input');

      if (element) {
        element.focus();
      }

      this.form.terms = value;
    },

  },

};
