export default {

  methods: {

    input_cut_mask(mask, value) {
      value = value || '';
      return '&nbsp;'.repeat(value.length) + mask.substr(value.length);
    },


    input_apply_mask(mask, schema, value) {
      let counter = 0;

      value = value || '';

      function traverse() {
        for (let i = 0; i < value.length; i++) {
          const regexp = new RegExp(schema[i] || '.');
          const symbol = mask.substr(i, 1) || '';

          if (!symbol) {
            value = value.substr(0, i);
            return;
          }

          if (symbol === 'x' && !regexp.test(value[i])) {
            value = value.substr(0, i) + value.substr(i + 1);

            if (counter++ < 100) {
              traverse();
            }

            return;
          }

          if (symbol !== 'x' && value[i] !== symbol) {
            value = value.substr(0, i) + symbol + value.substr(i);

            if (counter++ < 100) {
              traverse();
            }

            return;
          }
        }
      }

      traverse();

      return value;
    },

  },

};
