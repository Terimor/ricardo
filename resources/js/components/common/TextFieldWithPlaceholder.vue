<template>
  <label class="date-picker-manual" :class="{ 'with-error': invalid }">
    <span>{{label}}</span>
    <div class="date-picker-manual__input">
      <div class="placeholder" v-html="preparedPlaceholder"></div>
      <input
        @input="input"
        @focus="(e) => $emit('focus', e.target.value)"
        @blur="(e) => $emit('blur', e.target.value)"
        :maxlength="placeholder.length"
        :value="value"
        v-bind="rest" />
    </div>
    <div v-show="invalid" class="error">{{invalidMessage}}</div>
  </label>
</template>

<script>
  export default {
    name: 'TextFieldWithPlaceholder',
    props: ['label', 'value', 'format', 'rest', 'placeholder', 'invalid', 'invalidMessage', 'validation'],
    computed: {
      preparedPlaceholder () {
        const { length } = this.value
        return '&nbsp;'.repeat(length) + this.placeholder.slice(length)
      },
    },
    methods: {
      input (e) {
        this.$emit('input', e.target.value)
        this.validation.$touch()
      }
    }
  };
</script>

<style lang="scss">
    .date-picker-manual {
        width: 100%;

        &__input {
            position: relative;
            height: 40px;
            width: 100%;

            & > input, & > .placeholder {
                padding: 0 15px;
                font-size: 15px;
                font-family: monospace;
                display: flex;
                align-items: center;
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                width: 100%;
            }

            & > .placeholder {
                user-select: none;
                border: 1px solid #ddd;
                border-radius: 3px;
                background-color: rgba(255, 253, 228, 0.6);
                color: #ddd;
            }

            & > input {
                background: transparent;
                border: none;
                outline: none;
            }

        }

        &.with-error {
            & > span {
                color: red;
            }
        }
    }
</style>
