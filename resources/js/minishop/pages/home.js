import layout from '../layout';
import welcome from './home/welcome';
import products from './home/products';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      layout,
      welcome,
      products,
    ],

  });
});
