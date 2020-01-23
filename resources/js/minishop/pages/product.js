import layout from '../layout';
import image from './product/image';
import slider from './product/slider';
import quantity from './product/quantity';
import price_cart from './product/price_cart';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#app',


    mixins: [
      layout,
      image,
      slider,
      quantity,
      price_cart,
    ],

  });
});
