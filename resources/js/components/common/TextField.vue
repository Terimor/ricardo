<template>
  <label
    class="input-container scroll-when-error"
    :class="{
      [theme]: theme,
      invalid: invalid
    }">
    <span class="label">{{label}}</span>
    <div class="input-container__input">
      <div v-html="prefix" v-if="prefix" class="prefix"></div>
      <div @click="$emit('click-postfix')" v-html="postfix" v-if="postfix" class="postfix"></div>
      <input
        @blur="blur"
        @input="input"
        v-bind="rest"
        :style="{
          ...prefix && { ['padding-' + (isRTL ? 'right' : 'left')]: '45px' },
          ...postfix && { ['padding-' + (isRTL ? 'left' : 'right')]: '45px' },
          ...invalid && { 'animation': '0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s 1 normal both running shadow-drop-center-error' }
        }"
        :value="value">
    </div>
    <span class="error" v-show="invalid">{{validationMessage}}</span>
  </label>
</template>

<script>
export default {
  name: 'TextField',
  props: [
    'value',
    'label',
    'theme',
    'prefix',
    'postfix',
    'validation',
    'validationMessage',
    'forceInvalid',
    'rest'
  ],
  computed: {
    isRTL() {
      return !!document.querySelector('html[dir="rtl"]');
    },
    invalid() {
      return this.validation && this.validation.$dirty && !this.validation.$pending && this.validation.$invalid || this.forceInvalid;
    }
  },
  methods: {
    blur(event) {
      this.$emit('blur', event);
    },
    input (e) {
      this.$emit('input', e.target.value)
      if (this.validation) {
        this.validation.$touch()
      }
    }
  }
}
</script>

<style lang="scss">
.input-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin-bottom: 10px;
  position: relative;

  &.invalid {
    .label {
      color: #e74c3c;
    }
  }

  &__input {
    position: relative;
  }

  input {
    outline: none;
    color: rgb(85, 85, 85);
    width: 100%;
  }

  .label {
    margin-bottom: 6px;
  }

  .prefix {
    position: absolute;
    bottom: 0;
    left: 3px;
    height: 40px;
    width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px;

    [dir="rtl"] & {
      left: auto;
      right: 3px;
    }
  }

  .postfix {
    position: absolute;
    bottom: 0;
    right: 3px;
    height: 40px;
    width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px;

    [dir="rtl"] & {
      left: 3px;
      right: auto;
    }
  }

  &.variant-1 {
    input {
      border: 1px solid #ddd;
      border-radius: 3px;
      background-color: rgba(255,253,228,.6);
      height: 40px;
      padding: 0 15px;

      &:focus {
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
        border-color: #409EFF;
      }
    }
  }
}
</style>
