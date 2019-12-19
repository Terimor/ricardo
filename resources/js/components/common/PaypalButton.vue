<template>
  <div class="paypal-button-container" v-if="0">
    <div id="paypal-button"></div>
    <div class="paypal-shim" :class="{ 'active': !isSubmitted }">
      <div v-if="isSubmitted" class="disabled"></div>
      <div class="title"><slot /></div>
      <img class="image" :src="$root.cdn_url + '/assets/images/paypal-highq.png'" />
    </div>
  </div>
</template>

<script>
  import wait from '../../utils/wait';

  export default {
    name: 'PaypalButton',
    props: [
      'createOrder',
      'onApprove',
      '$vterms',
      '$vdeal',
    ],

    data() {
      return {
        isSubmitted: false,
        action: null,
      };
    },

    watch: {
      '$vdeal.$invalid'() {
        if (this.action && this.isValid()) {
          this.action.enable();
        }
      },
      '$vterms.$invalid'() {
        if (this.action && this.isValid()) {
          this.action.enable();
        }
      },
    },

    mounted () {
      wait(
        () => !!window.paypal,
        () => this.initButton(),
      );
    },

    methods: {
      isValid() {
        return (!this.$vdeal || !this.$vdeal.$invalid) && (!this.$vterms || !this.$vterms.$invalid);
      },
      initButton () {
        const { createOrder, onApprove } = this;
        const that = this;

        paypal.Buttons({
          onInit(data, actions) {
            that.action = actions;

            if (!that.isValid()) {
              actions.disable();
            }
          },

          createOrder(data, actions) {
            that.isSubmitted = true;

            return createOrder()
              .then(res => {
                return res && res.id || null;
              });
          },

          onClick () {
            that.$emit('click', true);
          },

          onApprove (data, actions) {
            return onApprove(data)
              .then(() => {
                setTimeout(() => that.isSubmitted = false, 1000);
              });
          },

          onError(err) {
            that.isSubmitted = false;
          },

          onCancel(data, actions) {
            that.isSubmitted = false;
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
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1000;

      &.active {
        pointer-events: none;
      }

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

        [dir="rtl"] & {
          content: '\f053';
          border-radius: 50% 0 0 50%;
          left: auto;
          right: 0;
        }
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

        [dir="rtl"] & {
          margin-left: 0;
          margin-right: 10px;
        }
      }

      .disabled {
        background-color: #fff;
        bottom: 0;
        left: 0;
        opacity: .5;
        position: absolute;
        right: 0;
        top: 0;
        z-index: 1;
      }
    }

    &:hover {
      .paypal-shim.active {
        background-image: linear-gradient(#f9b421, #fff0a8);

        &:before {
          opacity: 1;
          width: 30px;
        }
      }
    }
  }

  .tpl-emc1, .tpl-emc1b {
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
