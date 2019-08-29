<template>
    <div class="paypal-button-container"></div>
</template>

<script>
  export default {
    name: 'PaypalButton',
    props: ['createOrder', 'onApprove', '$v'],
    methods: {
      initButton () {
        const { createOrder, onApprove } = this;

        paypal.Buttons({
          createOrder: () => {
            if (this.$v.required && this.$v.$dirty) {
              return createOrder();
            } else {
              this.$emit('click', false);
            }
          },
          onApprove: function(data) {
            if (this.$v.$dirty) {
              return onApprove(data);
            }
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
