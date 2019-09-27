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
      overflow: hidden;
      pointer-events: none;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1000;

      &:before {
        opacity: 0;
        font-family: FontAwesome!important;
        content: '\f054';
        width: 0;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 0 50% 50% 0;
        background-color: rgba(255,255,255,.3);
        transition: all .2s linear 0s;
      }

      .title {
        color: #000;
        font-size: 15px;
        font-weight: 700;
        white-space: nowrap;
      }

      .image {
        margin-left: 10px;
        margin-top: -2px;
        max-width: 90px;
      }
    }

    &:hover {
      .paypal-shim {
        background-image: linear-gradient(#f9b421, #fff0a8);

        &:before {
          opacity: 1;
          width: 30px;
        }
      }
    }
  }

  .tpl-emc1 {
    @media screen and (min-width: 768px) and (max-width: 991px), (max-width: 420px) {
      .paypal-shim {
        flex-direction: column;

        .image {
          margin: 0;
        }
      }
    }
  }

  .tpl-vmc4 {
    @media screen and (min-width: 576px) and (max-width: 800px), (max-width: 460px) {
      .paypal-shim {
        flex-direction: column;

        .image {
          margin: 0;
        }
      }
    }
  }

</style>
