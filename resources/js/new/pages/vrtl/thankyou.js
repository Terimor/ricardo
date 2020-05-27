import app from '../../app';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#thank-you-vrtl',

    data () {
      return {
        tabActive: 'PRODUCT'
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
      },

      productFilePreviewClick: e => {
        let target = e.target;

        while (!target.classList.contains('product-file-image-preview')) {
          target = target.parentNode;
        }

        target.style.display = 'none';

        var content = target.nextElementSibling;

        content.classList.add('active');
      }
    },

    mixins: [
      app,
    ],

  });
});
