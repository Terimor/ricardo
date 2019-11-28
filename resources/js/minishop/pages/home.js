import layout from '../layout';
import welcome from './home/welcome';
import products from './home/products';


const deps = [
  'vue',
];


js_deps.wait(deps, () => {
  new Vue({

    el: '#app',


    mixins: [
      layout,
      welcome,
      products,
    ],

  });
});
