<template>
  <label class="label-container-radio radio-button-deal">
    <input type="radio" :checked="+item.value === value" name="radio" :value="+item.value">
    <div class="label-container-radio__label">
      <span>{{item.text}}</span>
      <span>${{item.price}}</span>
    </div>
    <div v-if="item.value !== 1">${{Math.round((item.price / item.totalQuantity * 100) / 100)}} / Unit</div>
    <span class="checkmark"></span>
  </label>
</template>

<script>
	export default {
		name: 'radio-button-item-deal',
		props: [
			'item',
			'value'
		],
		methods: {
			input(e) {
				return this.$emit('input', e.target.value)
			}
		}
	}
</script>

<style lang="scss">
  .radio-button-deal {
    .label-container-radio__label {
      display: flex;
      justify-content: space-between;
    }
  }
</style>
