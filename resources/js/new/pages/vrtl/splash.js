import app from '../../app';


js_deps.wait(['vue'], () => {
  new Vue({

    el: '#splash-vrtl',

    mixins: [
      app
    ],

    data () {
      return {
        countdownEndDate: new Date(new Date().getTime() + 14 * 60000),
        countdownString: ''
      }
    },

    computed: {
      countdownValue () {
        return this.countdownString;
      }
    },

    methods: {
      countdownStart () {
        const countdownInterval = setInterval(() => {

          // Get today's date and time
          const now = new Date().getTime();
            
          // Find the distance between now and the count down date
          const distance = this.countdownEndDate - now;
            
          // Time calculations for minutes and seconds
          const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
          // save the result
          this.countdownString = minutes + ":" + seconds;
            
          // If the count down is over, write some text 
          if (distance < 0) {
            clearInterval(countdownInterval);
            this.countdownString = "";
          }
        }, 1000);
      }
    },

    created () {
      this.countdownStart();
    }

  });
});
