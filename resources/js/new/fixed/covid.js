export default {

  data() {
    return {
      covid_fixed_visible: true,
    };
  },


  methods: {

    covid_fixed_close() {
      this.covid_fixed_visible = false;
      setTimeout(() => this.fixed_height_calc(), 1);
    },

  },

};
