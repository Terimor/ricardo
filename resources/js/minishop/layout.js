import './scripts/sentry';
import header from './regions/header';
import request from './utils/request';


export default {

  mixins: [
    header,
    request,
  ],

};
