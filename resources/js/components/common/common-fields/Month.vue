<template>
  
  <select-field
    :standart="true"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :list="list"
    :rest="{
      placeholder: textPlaceholder,
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textRequired() {
        return this.$t('checkout.payment_form.card_valid_month.required');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.card_valid_month.placeholder');
      },

      list() {
        const months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        return months.map(value => {
          return {
            text: value,
            label: value,
            value,
          };
        });
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
