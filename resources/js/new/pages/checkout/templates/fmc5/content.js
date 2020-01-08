import step1 from './content/left/step1';
import step2 from './content/left/step2';
import step3 from './content/left/step3';
import buttons from './content/left/buttons';


export default {

  mixins: [
    step1,
    step2,
    step3,
    buttons,
  ],


  data() {
    return {
      step: 1,
    };
  },


  created() {
    this.content_check_3ds_failure();
  },


  methods: {

    content_check_3ds_failure() {
      if (js_query_params['3ds'] === 'failure') {
        setTimeout(() => this.scroll_to_ref('payment_error'), 1000);
        this.step = 3;
      }
    },

  },
  
};
