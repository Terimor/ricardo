<template>
  
  <text-field
    id="email-field"
    v-model="form[name]"
    :validation="$v"
    :validationMessage="validationMessage"
    :warningMessage="warningMessage"
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
    :tabindex="tabindex"
    :order="order"
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
      'tabindex',
      'order',
      '$v',
    ],


    data() {
      return {
        validationMessage: null,
        warningMessage: null,
        forceInvalid: false,
        suggestion: null,
      };
    },


    created() {
      this.validationMessage = this.textRequired;
    },


    computed: {

      textLabel() {
        return this.$t('checkout.payment_form.email');
      },

      textRequired() {
        return this.$t('checkout.payment_form.email.required');
      },

      textInvalid() {
        return this.$t('checkout.payment_form.email.invalid');
      },

      textSuggestion() {
        return this.$t('checkout.payment_form.email.suggestion', {
          email: '<a href="#" class="suggestion">' + this.suggestion + '</a>',
        });
      },

      textWarning() {
        return this.$t('checkout.payment_form.email.warning');
      },

      textDisposable() {
        return this.$t('checkout.payment_form.email.disposable');
      },

    },


    methods: {

      input() {
        const value = this.form[this.name];

        this.form.emailForceInvalid = false;

        this.validationMessage = this.$v.$invalid
          ? !this.$v.required
            ? this.textRequired
            : this.textInvalid
          : null;

        this.warningMessage = null;
        this.forceInvalid = false;
        this.suggestion = null;

        if (cache[value]) {
          this.apply(cache[value]);
        }
      },

      blur() {
        const value = this.form[this.name];

        if (this.$v.$invalid) {
          return;
        }

        if (cache[value]) {
          return this.apply(cache[value]);
        }

        fetch('/validate-email?email=' + value)
          .then(res => res.json())
          .then(res => {
            cache[value] = res;
            this.apply(res);
          })
          .catch(err => {
            
          });
      },

      apply(res) {
        if (res.block) {
          this.form.emailForceInvalid = true;
          return;
        }

        if (!res.valid) {
          this.forceInvalid = true;
          this.form.emailForceInvalid = true;
          this.validationMessage = this.textInvalid;

          return;
        }

        if (res.suggest) {
          this.suggestion = res.suggest;
          this.warningMessage = this.textSuggestion;
          setTimeout(() => this.bindSuggestion(), 100);
          return;
        }

        if (res.warning) {
          this.warningMessage = this.textWarning;
          return;
        }

        if (res.disposable) {
          this.warningMessage = this.textDisposable;
          return;
        }
      },

      bindSuggestion() {
        const element = this.$el.querySelector('.suggestion');

        if (element && !element.clickListener) {
          element.clickListener = true;

          element.addEventListener('click', event => {
            event.preventDefault();

            this.form[this.name] = this.suggestion;

            setTimeout(() => {
              this.input();
              this.blur();
            }, 100);
          });
        }
      },

    },

  };

</script>


<style lang="scss" scoped>

</style>
