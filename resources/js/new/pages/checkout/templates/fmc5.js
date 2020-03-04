import checkout from '../../checkout';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      checkout,
    ],


    data() {
      return {
        step: 1,
      };
    },


    validations() {
      return {
        ...checkout.validations.call(this),
      };
    },


    created() {
      if (js_query_params['3ds_restore']) {
        this.step = 3;
      }
    },


    methods: {

      back_click() {
        this.step--;
        setTimeout(() => this.scroll_to_ref('step'), 100);
      },

      next_click() {
        if (this.step === 1) {
          return this.step1_submit();
        }

        if (this.step === 2) {
          return this.step2_submit();
        }

        if (this.step === 3) {
          return this.step3_submit();
        }
      },

      next_bottom_click() {
        this.scroll_to_ref('step');
      },

      step1_submit() {
        const fields = [
          'deal',
          'variant',
          'installments',
        ];

        if (!this.form_check_fields_valid(fields)) {
          return setTimeout(() => this.scroll_to_error(), 100);
        }

        this.step++;

        setTimeout(() => this.scroll_to_ref('step'), 100);
      },

      step2_submit() {
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

        this.step++;

        setTimeout(() => this.scroll_to_ref('step'), 100);
      },

      step3_submit() {
        if (this.is_submitted) {
          return;
        }

        const fields = [
          'warranty',
          'card_holder',
          'card_type',
          'card_number',
          'card_date',
          'card_cvv',
          'document_type',
          'document_number',
          'terms',
        ];

        if (!this.form_check_fields_valid(fields)) {
          return setTimeout(() => this.scroll_to_error(), 100);
        }

        this.credit_card_create_order();
      },

    },

  });
});
