<template>

  <text-field
    id="last-name-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'family-name',
      name: 'family-name',
    }"
    theme="variant-1"
    :tabindex="tabindex"
    :order="order"
    @blur="blur" />

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
        return this.$t('checkout.payment_form.last_name');
      },

      textRequired() {
        return this.$t('checkout.payment_form.last_name.required');
      },

    },


    methods: {

      blur() {
        this.$emit('check_for_leads_request');
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
