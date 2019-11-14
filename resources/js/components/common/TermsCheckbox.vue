<template>

  <div
    class="terms-checkbox"
    :class="classObject">

    <label
      for="terms-checkbox"
      class="label-container-checkbox">

      <input 
        type="checkbox"
        id="terms-checkbox"
        v-model="form.terms" />

      <span
        class="checkmark"></span>

      <a
        href="/terms"
        target="_blank"
        v-html="textTermsCheckbox"></a>

    </label>

  </div>
  
</template>


<script>
  
  export default {

    name: 'TermsCheckbox',


    props: [
      'form',
      '$v',
    ],


    computed: {

      classObject() {
        return {
          invalid: this.$v.form.terms.$dirty && this.$v.form.terms.$invalid,
        };
      },

      textTermsCheckbox() {
        return this.$t('checkout.terms_checkbox');
      },

    },

  };

</script>


<style lang="scss" scoped>

  label {
    font-size: 16px;
    line-height: 1.5;
  }

  .checkmark {
    top: 1px;

    .invalid & {
      animation: 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error;
    }
  }

  a {
    color: #333;
    text-decoration: none;

    .invalid & {
      color: #e74c3c;
    }
  }

</style>
