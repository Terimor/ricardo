import checkout from '../../checkout';
import section4 from './slimeazy/section4';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      checkout,
      section4,
    ],


    validations() {
      return {
        ...checkout.validations.call(this),
      };
    },


    data() {
      return {
        step: 1,
      };
    },


    methods: {

      step1_submit() {
        const fields = [
          'first_name',
          'last_name',
          'email',
          'phone',
          'street',
          'building',
          'complement',
          'district',
          'city',
          'state',
          'zipcode',
          'country',
        ];

        if (!this.form_check_fields_valid(fields)) {
          return setTimeout(() => this.scroll_to_error(), 100);
        }
      },

    },

  });
});
