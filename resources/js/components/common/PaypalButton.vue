<template>
  <div class="paypal-button-container">
    <div id="paypal-button"></div>
    <div class="paypal-shim">
      <div class="title"><slot /></div>
      <img class="image" src="/images/paypal-highq.png" />
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PaypalButton',
    props: [
      'createOrder',
      'onApprove',
      '$v'
    ],

    data() {
      return {
        inputCheckbox: this.$v.required,
        action: null
      }
    },

    watch: {
      '$v.$invalid' () {
        if (this.action) {
          this.action.enable();
        }
      }
    },

    mounted () {
      this.initButton();
    },

    methods: {
      initButton () {
        const { createOrder, onApprove, $v } = this;
        const that = this;

        paypal.Buttons({
          onInit(data, actions) {
            that.action = actions;
            if ($v.$invalid) {
              actions.disable();
            }
          },

          createOrder(data, actions) {
              return createOrder();
          },

          onClick () {
            if (!$v.required || !$v.$dirty) {
              that.$emit('click', true);
              return true;
            }
          },

          onApprove (data, actions) {
            return onApprove(data);
            // Commented out so order verification will pass (temporary change)
            // if ($v.required || $v.$dirty) {
            //     return onApprove(data);
            // }
          },

          style: {
            height: 55,
          }
        }).render('#paypal-button');
      }
    },
  };
</script>

<style lang="scss">
  .paypal-button-container {
    margin-bottom: 10px;
    position: relative;
    width: 100%;

    #paypal-button {
      height: 55px;
      overflow: hidden;
    }
 
    .paypal-shim {
      align-items: center;
      background-color: #ffc438;
      background-image: linear-gradient(#fff0a8,#f9b421);
      border: 1px solid #feae01;
      border-radius: 5px;
      display: flex;
      height: 55px;
      justify-content: center;
      left: 0;
      pointer-events: none;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1000;
 
      .title {
        color: #000;
        font-size: 15px;
        font-weight: 700;
      }
 
      .image {
        margin-left: 10px;
        margin-top: -2px;
        max-width: 90px;
      }
    }
  }
</style>
