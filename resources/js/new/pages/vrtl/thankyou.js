import app from '../../app';
import { faCaretRight, faAngleDown } from '@fortawesome/free-solid-svg-icons'


js_deps.wait(['vue'], () => {
  require('../../../bootstrap');

  new Vue({

    el: '#thank-you-vrtl',

    computed: {
      caretRight () {
        return faCaretRight
      },

      angleDown () {
        return faAngleDown
      }
    },

    mixins: [
      app,
    ],

  });
});
