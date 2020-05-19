import app from '../../app';
import { faCaretRight, faAngleDown } from '@fortawesome/free-solid-svg-icons'


js_deps.wait(['vue'], () => {
  require('../../../bootstrap');

  new Vue({

    el: '#thank-you-vrtl',

    data () {
      return {
        tabActive: 'PRODUCT'
      }
    },

    computed: {
      caretRight () {
        return faCaretRight
      },

      angleDown () {
        return faAngleDown
      }
    },

    methods: {
      setTab (tab) {
        this.tabActive = tab;
      },

      collapseHeadClick: e => {
        let target = e.target;

        while (!target.classList.contains('product-file-collapse-head')) {
          target = target.parentNode;
        }

        target.classList.toggle('active');

        var content = target.nextElementSibling;
    
        if (content.style.maxHeight){
          content.style.maxHeight = null;
          content.style.padding = null;
        } else {
          content.style.maxHeight = content.scrollHeight + "px";
          content.style.padding = "10px 0";
        }
      }
    },

    mixins: [
      app,
    ],

  });
});
