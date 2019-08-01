<template>
  <label class="input-container" :class="theme">
    <span class="label">{{label}}</span>
    <div class="input-container__input">
      <div v-html="prefix" v-if="prefix" class="prefix"></div>
      <div @click="$emit('click-postfix')" v-html="postfix" v-if="postfix" class="postfix"></div>
      <input
        @input="input"
        type="text"
        :style="{
          ...prefix && { 'padding-left': '45px' },
          ...postfix && { 'padding-right': '45px' },
        }"
        :value="value">
    </div>
  </label>
</template>

<script>
export default {
  name: 'TextField',
  props: ['value', 'label', 'theme', 'prefix', 'postfix'],
  methods: {
    input (e) {
      this.$emit('input', e.target.value)
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
