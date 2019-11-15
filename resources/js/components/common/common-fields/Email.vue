<template>
  
  <text-field
    id="email-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="textRequired"
    :warningMessage="warningMessage"
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
        warningMessage: null,
      };
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.email');
      },

      textRequired() {
        return this.$t('checkout.payment_form.email.required');
      },

      textWarning() {
        return this.$t('checkout.payment_form.email.invalid');
      },

    },


    methods: {

      input() {
        const value = this.form[this.name];

        this.warningMessage = cache[value] !== undefined
          ? !cache[value]
            ? this.textWarning
            : null
          : null;
      },

      blur() {
        const value = this.form[this.name];

        if (this.$v.$invalid) {
          return;
        }

        if (cache[value] !== undefined) {
          return this.warningMessage = !cache[value]
            ? this.textWarning
            : null;
        }

        fetch('/validate-email?email=' + value)
          .then(res => res.json())
          .then(res => {
            cache[value] = res.success;

            this.warningMessage = !res.success
              ? this.textWarning
              : null;
          })
          .catch(err => {
            this.warningMessage = this.textWarning;
          });
      },

    },

  };

</script>


<style lang="scss" scoped>
  
</style>
