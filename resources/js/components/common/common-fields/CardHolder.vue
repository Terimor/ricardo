<template>

  <text-field
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
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
      'placeholder',
      'tabindex',
      'order',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.card_holder');
      },

      textRequired() {
        return this.$t('checkout.payment_form.card_holder.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
