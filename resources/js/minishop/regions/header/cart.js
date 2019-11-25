let cart = localStorage.getItem('minishop:cart');

try {
  cart = JSON.parse(cart);

  if (!Array.isArray(cart)) {
    cart = [];
  }
}
catch (err) {
  cart = [];
}


export default {

  data: {
    cart,
  },


  watch: {

    cart() {
      localStorage.setItem('minishop:cart', JSON.stringify(this.cart));
    },

  },


  methods: {

    add_to_cart(id) {
      this.cart.push(id);
    },

  },

};
