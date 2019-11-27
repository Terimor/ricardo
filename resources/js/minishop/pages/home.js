import layout from '../layout';


const deps = [
  'vue',
];


js_deps.wait(deps, () => {
  new Vue({

    el: '#app',


    mixins: [
      layout,
    ],


    methods: {

      goto_checkout(sku_code) {
        this.goto('/checkout?product=' + sku_code);
      },

    },

  });
});
