let interval = null;
const start_minutes = 13;
const start_seconds = Math.floor(Math.random() * 59) + 1;


export default {

  data() {
    return {
      timer_time: ('0' + start_minutes).slice(-2) + ':' + ('0' + start_seconds).slice(-2),
      timer_finish_time: null,
    };
  },


  created() {
    if (this.timer_enabled) {
      this.timer_init();
    }
  },


  computed: {

    timer_enabled() {
      return js_query_params.show_timer === '{timer}' || +js_query_params.show_timer === 1;
    },

  },


  methods: {

    timer_init() {
      const timeout = this.preloader_enabled ? 10000 : 0;

      setTimeout(() => {
        this.timer_finish_time = new Date(new Date().getTime() + start_minutes * 60 * 1000 + (start_seconds + 1) * 1000);
        interval = setInterval(this.timer_decrement, 1000);
      }, timeout);
    },

    timer_decrement() {
      let diff = this.timer_finish_time.getTime() - new Date().getTime();
      diff = Math.floor(diff / 1000);

      const minutes = Math.floor(diff / 60) % 60;
      const seconds = diff % 60;

      this.timer_time = [minutes, seconds]
        .map(numbers => ('0' + numbers).slice(-2))
        .join(':');

      if (this.timer_time === '00:00') {
        clearInterval(interval);
      }
    },

  },

};
