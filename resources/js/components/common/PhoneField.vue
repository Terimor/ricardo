<template>
  <label
    class="phone-input-container scroll-when-error"
    :style="{ order: order || null }"
    :class="{
      [theme]: theme,
      invalid: invalid
    }">
    <span class="label">{{label}}</span>
    <input
      :tabindex="tabindex || null"
      :style="{
        ...invalid && { 'animation': '0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error' }
      }"
      type="tel"
      v-bind="rest"
      @input="input"
      :value="value"
      :id="id">
    <span v-show="invalid" class="error">{{validationMessage}}</span>
  </label>
</template>

<script>
  let idCounter = 0;

  import wait from '../../utils/wait';

  export default {
    name: 'PhoneField',
    props: ['value', 'label', 'theme', 'tabindex', 'order', 'validation', 'validationMessage', 'countryCode', 'rest'],

    data() {
      return {
        id: 'phone-' + idCounter++,
        selector: null,
      };
    },

    computed: {
      invalid () {
        return this.validation && this.validation.$dirty && this.validation.$invalid
      }
    },

    methods: {
      checkPadding() {
        if (document.querySelector('html[dir="rtl"]') && this.selector.style.paddingLeft) {
          this.selector.style.paddingRight = this.selector.style.paddingLeft;
        }
      },
      input (e) {
        this.$emit('input', e.target.value)
        if (this.validation) {
          this.validation.$touch()
        }
      }
    },

    mounted () {
      wait(
        () => {
          const linkTag = document.querySelector('#intlTelInputCss');
          return this.$root._isMounted && (!linkTag || linkTag.media !== 'none');
        },
        () => {
          setTimeout(() => {
            this.selector = document.querySelector(`#${this.id}`)

            window.intlTelInput(this.selector, {
              initialCountry: this.countryCode,
              separateDialCode: true
            })

            this.selector.addEventListener('countrychange', () => {
              this.$emit('onCountryChange', window.intlTelInputGlobals.getInstance(this.selector).getSelectedCountryData())
              this.checkPadding();
            });

            this.checkPadding();
          }, 100);
        },
      )
    }
  }
</script>

<style lang="scss">
  .phone-input-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;
    position: relative;

      &.invalid {
        .label, .error {
          color: #e74c3c;
        }
      }

    input {
      outline: none;
      width: 100%;
      color: rgb(85, 85, 85);
    }

    .iti__selected-flag {
      outline: none;
    }

    .label {
      margin-bottom: 6px;
    }

    &.variant-1 {
      input {
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: rgba(255, 253, 228, .6);
        height: 40px;
        padding-right: 15px;

        &:focus {
          box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
          border-color: #409EFF;
        }

        [dir="rtl"] & {
          padding-left: 15px!important;
        }
      }
    }

    .iti__country {
      display: flex;
      align-items: center;
    }

    .iti__selected-dial-code, .iti__arrow {
      [dir="rtl"] & {
        margin-left: 0;
        margin-right: 6px;
      }
    }

    .iti__flag-box, .iti__country-name {
      [dir="rtl"] & {
        margin-left: 6px;
        margin-right: 0;
      }
    }
  }
</style>
