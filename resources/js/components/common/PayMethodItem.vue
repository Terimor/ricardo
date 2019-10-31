<template>
  <label class="pay-method-item">
    <img
      alt="Pay Image"
      :src="input.imgUrl"
      :style="{
        'border':
          checked
          ? '2px solid rgba(255,59,0,.93)'
          :'none'
      }">
    <input
      type="radio"
      name="radio"
      :checked="checked"
      :value="input.value"
      @change="onInput"
    >
  </label>
</template>
<script>
	export default {
		name: 'PayMethodItem',
		props: ['value', 'input'],
    computed: {
			checked () {
				return this.input.value === this.value
      }
    },
    methods: {
      onInput(e) {
        if (/Edge/.test(navigator.userAgent) && this.$parent.onInput) {
          this.$parent.onInput(e);
        }
      },
    },
	}
</script>
<style lang="scss">
  .pay-method-item {
    cursor: pointer;
    img {
      max-height: 30px;
    }

    input {
      display: none;
    }
  }

</style>
