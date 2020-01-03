<template>

  <text-field
    id="building-field"
    v-if="extraFields.building"
    v-model="form.building"
    :validation="$v.building"
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

    name: 'Building',

    props: [
      'placeholder',
      'extraFields',
      'tabindex',
      'order',
      'form',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.building');
      },

      textRequired() {
        return this.$t('checkout.payment_form.building.required');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
