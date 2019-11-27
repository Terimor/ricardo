import cart from './services/cart';
import document from './utils/document';
import request from './utils/request';
import toggler from './regions/header/toggler';
import freshchat from './regions/fixed/freshchat';
import support from './regions/fixed/support';


export default {

  mixins: [
    cart,
    document,
    request,
    toggler,
    freshchat,
    support,
  ],

};
