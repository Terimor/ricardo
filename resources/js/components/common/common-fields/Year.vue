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
    theme="variant-1" />

</template>


<script>

  export default {

    props: [
      'form',
      'name',
      '$v',
    ],


    computed: {

      textRequired() {
        return this.$t('checkout.payment_form.card_valid_year.required');
      },

      textPlaceholder() {
        return this.$t('checkout.payment_form.card_valid_year.placeholder');
      },

      list() {
        const year = new Date().getFullYear() || 2020;
        const years = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        return years.map(value => {
          return {
            text: value + year - 1,
            label: value + year - 1,
            value: value + year - 1,
          };
        });
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
