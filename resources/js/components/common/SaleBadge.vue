<template>

  <div
    class="sale-badge dynamic-sale-badge">

    <template
      v-if="!isBlackFriday">

      <div
        class="dynamic-sale-badge__background"></div>

      <div
        v-html="textSaleBadge"
        :style="styleFontSize"
        class="dynamic-sale-badge__container"></div>

    </template>

    <img
      v-else
      class="black-friday"
      :src="$root.cdnUrl + '/assets/images/BF_badge_V2.png'" />

  </div>

</template>


<script>

  export default {

    name: 'SaleBadge',

    computed: {

      textSaleBadge() {
        return this.$t('checkout.dynamic_sale_badge');
      },

      styleFontSize() {
        return {
          'font-size': (this.fontSizes[checkoutData.langCode] || 12) + 'px',
        };
      },

      isBlackFriday() {
        return !!window.blackFridayEnabled;
      },

      fontSizes() {
        return {
          en: 22,
          br: 14,
          ru: 16,
          ja: 18,
          de: 18,
          es: 12,
          fi: 15,
          pl: 18,
          my: 15,
          hu: 12,
          tr: 18,
          id: 18,
          ar: 20,
          ko: 20,
          no: 18,
          sv: 18,
          fr: 13,
          it: 14,
          nl: 17,
          pt: 14,
          da: 20,
          cs: 20,
          he: 20,
          el: 13,
          hr: 16,
          zh: 16,
          sl: 16,
          jv: 22,
          th: 20,
          af: 18,
          hi: 18,
          bg: 14,
          ro: 13,
          ee: 14,
          lv: 17,
          lt: 14,
          sk: 18,
          mt: 20,
          ur: 22,
          bn: 20,
          vi: 20,
          sr: 15,
          is: 14,
          uk: 16,
          tw: 17,
        };
      },

    },

  };

</script>


<style lang="scss" scoped>

  .sale-badge {
    width: 85px;
    height: 85px;
    margin-left: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    color: #fff;
    position: relative;
  }

  .dynamic-sale-badge__background {
    position: absolute;
    background: #e74c3c;
    border-radius: 50%;
    padding: 5px;
    border: 2px dashed #fff;
    box-shadow: 0 0 0 5px #e74c3c;
    width: 85px;
    height: 85px;

    [dir="rtl"] & {
      animation-direction: reverse;
    }
  }

  .dynamic-sale-badge__container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-size: 12px;
    text-align: center;
    transform: rotate(349deg);
    position: absolute;
    width: 85px;
    height: 85px;

    [dir="rtl"] & {
      transform: rotate(371deg);
    }
  }

  .black-friday {
    margin: -5px 0 0 -5px;
    max-width: 120%;
    height: auto;
  }

</style>
