<template>
    <div class="paypal-button-container"></div>
</template>

<script>
  export default {
    name: 'PaypalButton',
    props: ['createOrder', 'onApprove', '$v'],
    data() {
      return {
        inputCheckbox: this.$v.required,
        action: null
      }
    },
    methods: {
      initButton () {
        const { createOrder, onApprove, $v } = this;
        const that = this;

        paypal.Buttons({
          onInit(data, actions) {
            that.action = actions;
            if ($v.required || $v.$dirty) {
              actions.disable();
            } else {
              return createOrder();
            }
          },
          createOrder(data, actions) {
              return createOrder();
          },
          onClick () {
            if ($v.required || $v.$dirty) {
              return createOrder();
            } else {
              that.$emit('click', true);
              return true;
            }
          },
          onApprove (data, actions) {
            if ($v.required || $v.$dirty) {
              return onApprove(data);
            }
          },
          style: {
            label: 'buynow'
          }
        }).render('.paypal-button-container');
      }
    },
    watch: {
      '$v.required' () {
        this.action.enable();
      }
    },
    mounted () {
      this.initButton()
    }
  };
</script>

<style lang="scss">
.paypal-button-container {
    max-width: none;
    width: 100%;
    margin-bottom: 10px;
}
</style>
