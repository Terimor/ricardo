<template>
  <div class="payment-providers-apm">
    <div
      v-if="provider.is_apm"
      v-for="(provider, name) in $root.paymentMethods"
      :class="{ ['payment-provider-' + name]: true }"
      class="payment-provider"
      @click="select(name)"
    >
      <div
        class="radio"
        :class="{
          selected: value === name,
        }"
      />
      <div class="label">
        {{ provider.name }}
      </div>
      <img
        class="image lazy"
        :data-src="provider.logo.replace('-curved', '')"
      />
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PaymentProvidersAPM',
    props: [
      'value',
    ],
    methods: {
      select(name) {
        this.$emit('input', name);
      },
    },
  };
</script>


<style lang="scss" scoped>
  .payment-providers-apm {
    display: flex;
    flex-direction: column;
  }

  .payment-provider {
    align-items: center;
    background: linear-gradient(to bottom, #f7f8fa, #e7e9ec);
    border: 2px solid #000;
    cursor: pointer;
    display: flex;
    height: 56px;
    margin-bottom: 10px;
    position: relative;

    &:before {
      align-items: center;
      background-color: rgba(255,255,255,.3);
      border-radius: 0 50% 50% 0;
      content: "\F054";
      display: flex;
      font-family: FontAwesome !important;
      justify-content: center;
      height: 100%;
      left: 0;
      opacity: 0;
      position: absolute;
      top: 0;
      transition: all 0.2s linear 0s;
      width: 0;
      z-index: 1;

      [dir="rtl"] & {
        border-radius: 50% 0 0 50%;
        content: '\f053';
        left: auto;
        right: 0;
      }
    }

    &:hover {
      background: linear-gradient(to bottom, #e7e9ec, #f7f8fa);

      &:before {
        opacity: 1;
        width: 30px;
      }
    }
  }

  .radio {
    background-color: #fff;
    border: 1px solid #000;
    border-radius: 50%;
    height: 18px;
    margin-left: 13px;
    position: relative;
    width: 18px;

    [dir="rtl"] & {
      margin-left: 0;
      margin-right: 16px;
    }

    &:after {
      background-color: #000;
      border-radius: 100%;
      content: "";
      height: 12px;
      position: absolute;
      display: none;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 12px;
    }

    &.selected:after {
      display: block;
    }
  }

  .label {
    font-weight: bold;
    font-size: 16px;
    margin-left: 16px;
    margin-top: 2px;

    [dir="rtl"] & {
      margin-left: 0;
      margin-right: 16px;
    }
  }

  .image {
    margin-left: auto;
    margin-right: 8px;
    max-height: 44px;

    [dir="rtl"] & {
      margin-left: 8px;
      margin-right: auto;
    }
  }
</style>
