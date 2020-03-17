<template>
  <div
    v-if="$root.hasEPS"
    class="payment-provider-eps"
    @click="select"
  >
    <div
      class="radio"
      :class="{
        selected: value === 'eps',
      }"
    />
    <div class="label">
      EPS
    </div>
    <img
      class="image lazy"
      :data-src="$root.cdn_url + '/assets/images/cc-icons/eps.png'"
    />
  </div>
</template>

<script>
  export default {
    name: 'PaymentProviderRadioList',
    props: [
      'value',
    ],
    methods: {
      select() {
        this.$emit('input', 'eps');
      },
    },
  };
</script>


<style lang="scss" scoped>
  .payment-provider-eps {
    align-items: center;
    background: linear-gradient(to bottom, #f7f8fa, #e7e9ec);
    border: 2px solid #000;
    cursor: pointer;
    display: flex;
    height: 56px;
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
    }

    &:hover {
      background: linear-gradient(to bottom, #e7e9ec, #f7f8fa);

      &:before {
        opacity: 1;
        width: 30px;
      }
    }

    .radio {
      background-color: #fff;
      border: 1px solid #000;
      border-radius: 50%;
      height: 18px;
      margin-left: 16px;
      position: relative;
      width: 18px;

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
    }

    .image {
      margin-left: auto;
      margin-right: 8px;
      max-width: 72px;
    }
  }
</style>
