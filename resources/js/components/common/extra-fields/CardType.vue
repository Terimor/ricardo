<template>
  
  <select-field
    :standart="true"
    v-if="extraFields.card_type"
    v-model="form.card_type"
    :validation="$v.form.card_type"
    :validationMessage="textRequired"
    :list="cardTypeList"
    :label="textTitle"
    :rest="{
      placeholder: textPlaceholder,
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>
  
  export default {

    name: 'CardType',

    props: [
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textTitle() {
        return this.$t('checkout.payment_form.card_type.title');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_type.required');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.card_type.placeholder');
      },

      cardTypeList() {
        return this.extraFields.card_type.items.map(item => ({
          value: item.value,
          label: this.$t(item.phrase),
          text: this.$t(item.phrase),
        }));
      },

    },

  };

</script>


<style lang="scss">
  
</style>
