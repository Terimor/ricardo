const available = [1, 3, 5];
/*const available = Object.keys(js_data.product.prices)
  .filter(value => js_data.product.prices[value].value)
  .map(value => parseInt(value, 10));*/


export default {

  data() {
    return {
      quantity: 1,
    };
  },


  methods: {

    quantity_minus() {
      const index = available.indexOf(this.quantity) - 1;
      this.quantity = available[index] || this.quantity;
    },

    quantity_plus() {
      const index = available.indexOf(this.quantity) + 1;
      this.quantity = available[index] || this.quantity;
    },

  },

};
