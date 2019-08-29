<template>
    <div class="paypal-button-container"></div>
</template>

<script>
  export default {
    name: 'PaypalButton',
    props: ['createOrder', 'onApprove'],
    methods: {
      initButton () {
        const { createOrder, onApprove } = this;

        paypal.Buttons({
          createOrder: function() {
            return createOrder()
          },
          onApprove: function(data) {
            console.log(data)
            return onApprove(data)
          }
        }).render('.paypal-button-container');
      }
    },
    mounted () {
      this.initButton()
    }
  };
</script>

<style lang="scss">
.paypal-button-container {
    width: 100%;
    margin-bottom: 10px;
}
</style>
