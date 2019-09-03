const app = new Vue({
  el: "#promo",

  data: {
    selectedPlan: 'most-popular',
  },

  methods: {
    setSelectedPlan(plan) {
      this.selectedPlan = plan;

      console.log(this.$data)
    }
  }
})
