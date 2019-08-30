<template>
    <div class="paypal-button-container"></div>
</template>

<script>
  export default {
    name: 'PaypalButton',
    props: ['createOrder', 'onApprove', '$v'],
    methods: {
      initButton () {
        const { createOrder, onApprove, $v } = this;
        const that = this;

        paypal.Buttons({
          onClick () {
            if ($v.required && $v.$dirty) {
              return createOrder();
            } else {
              that.$emit('click', false);
              return false;
            }
          },
          onApprove (data) {
            if ($v.required && $v.$dirty) {
              return onApprove(data);
            }
          },
          style: {
            label: 'buynow'
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
