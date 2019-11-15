<template>
  
  <text-field
    id="email-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="validationMessage"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'email',
      name: 'email',
    }"
    theme="variant-1"
    @blur="blur" />

</template>


<script>
  
  export default {

    props: [
      'form',
      'name',
      'placeholder',
      '$v',
    ],


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.email');
      },

      validationMessage() {
        if (!this.$v.isValid) {
          return this.$t('checkout.payment_form.email.invalid');
        }

        return this.$t('checkout.payment_form.email.required');
      },

    },


    methods: {

      blur() {
        const value = this.form[this.name];

        this.form[this.name] = '';

        this.$nextTick(() => {
          this.form[this.name] = value;
        });
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
