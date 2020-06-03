import app from '../../app';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#thank-you-vrtl',

    data () {
      return {
        tabActive: 'PRODUCT',
        mediaShow: {}
      }
    },

    methods: {
      setTab (tab) {
        this.tabActive = tab;
      },

      collapseHeadClick: e => {
        e.preventDefault();

        let target = e.target;

        while (!target.classList.contains('product-file-collapse-head')) {
          target = target.parentNode;
        }

        var content = target.nextElementSibling;
    
        if (target.classList.contains('active')){
          content.style.maxHeight = '0';
          content.style.padding = '0';
        } else {
          content.style.maxHeight = content.scrollHeight + "px";
          content.style.padding = "10px 0";
        }

        target.classList.toggle('active');
      },

      productFilePreviewClick (e, mediaId) {
        e.preventDefault();

        console.log(mediaId)

        this.mediaShow = {
          ...this.mediaShow,
          [mediaId]: true
        };
      }
    },

    mixins: [
      app,
    ],

  });
});
