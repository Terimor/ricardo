<template>
  
  <text-field
    id="email-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="validationMessage"
    :forceInvalid="forceInvalid"
    :label="textLabel"
    :rest="{
      placeholder: placeholder
        ? textLabel
        : null,
      autocomplete: 'email',
      name: 'email',
    }"
    theme="variant-1"
    @input="input"
    @blur="blur" />

</template>


<script>

  let cache = {};


  export default {

    props: [
      'form',
      'name',
      'placeholder',
      '$v',
    ],


    data() {
      return {
        forceInvalid: false,
      };
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.email');
      },

      validationMessage() {
        if (this.forceInvalid) {
          return this.$t('checkout.payment_form.email.invalid');
        }

        return this.$t('checkout.payment_form.email.required');
      },

    },


    methods: {

      input() {
        const value = this.form[this.name];

        this.forceInvalid = cache[value] !== undefined
          ? !cache[value]
          : false;
      },

      blur() {
        const value = this.form[this.name];

        if (this.$v.$invalid) {
          return;
        }

        if (cache[value] !== undefined) {
          return this.forceInvalid = !cache[value];
        }

        fetch('/validate-email?email=' + value)
          .then(res => res.json())
          .then(res => {
            cache[value] = res.success;
            this.forceInvalid = !res.success;
          })
          .catch(err => {
            this.forceInvalid = true;
          });
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
