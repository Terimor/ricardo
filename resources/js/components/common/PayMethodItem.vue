<template>
  <label class="pay-method-item">
    <img :src="input.imgUrl" alt="Pay Image" :style="{
      'border': checked ? '2px solid rgba(255,59,0,.93)' : 'none'
    }">
    <input type="radio"
           name="radio"
           :checked="checked"
           :value="input.value">
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
    }
	}
</script>
<style lang="scss">
  .pay-method-item {
    img {
      max-height: 30px;
    }

    input {
      display: none;
    }
  }

</style>