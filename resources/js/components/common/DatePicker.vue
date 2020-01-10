<template>
  <div class="date-picker-field">
      <label :for="id">{{ label }}</label>
      <el-date-picker
        @input="input"
        :id="id"
        v-bind="rest"
        :picker-options="pickerOptions"
        :value="value"
        :class="{ [theme]: theme }"
        type="date"/>
  </div>
</template>

<script>
  export default {
    name: 'DatePicker',
    props: [
      'value',
      'theme',
      'rest',
      'label',
      'pickerOptions'
    ],

    data () {
      return {
        value1: null
      }
    },

    computed: {
      id () {
        return 'date-picker-' + this.label.replace(/[ ]/g, '-').toLowerCase();
      }
    },

    methods: {
      input (val) {
        this.$emit('input', val);
      }
    }
  };
</script>

<style lang="scss">
.date-picker-field {
    width: 100%;

    .el-date-editor.el-input {
        width: 100%;
        margin-top: 5px;

        &.variant-1 {

            .el-input__inner {
                border: 1px solid #ddd;
                border-radius: 3px;
                background-color: rgba(255, 253, 228, 0.6);
                height: 40px;

                &:focus {
                    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(102, 175, 233, 0.6);
                    border-color: #409EFF;
                }
            }
        }
    }
}
</style>
