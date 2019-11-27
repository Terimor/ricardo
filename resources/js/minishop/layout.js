import cart from './services/cart';
import request from './utils/request';
import toggler from './regions/header/toggler';
import freshchat from './regions/fixed/freshchat';
import support from './regions/fixed/support';


export default {

  mixins: [
    cart,
    request,
    toggler,
    freshchat,
    support,
  ],

};
