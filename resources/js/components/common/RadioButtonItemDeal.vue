<template>
  <label class="label-container-radio radio-button-deal"
          :class="[`item-${item.value}`, {disabled: item.isOutOfStock}]">
    <img class="share" src="/images/share.png" v-if="showShareArrow">
    <input type="radio"
           :checked="item.value === value"
           name="radio"
           @input="input"
           :value="item.value"
           :disabled="item.isOutOfStock">
    <div class="label-container-radio__label">
      <div>
        <div class="red">
          {{item.discountName}}
        </div>
        <div>
          {{item.text}}
        </div>
        <div class="label-container-radio__discount" v-html="item.discountText">
        </div>
      </div>
      <div class="price">
        <div class="bestseller" v-if="isBestseller()">
          <img src="/images/best-seller-checkout4.png" alt="Bestseller">
        </div>
        <span>
          {{ item.newPrice || item.price }}
        </span>
      </div>
    </div>
    <div class="label-container-radio__subtitle" v-if="showPerUnitPrice && item.value !== 1">
      {{item.pricePerUnit ? `${item.pricePerUnit['1']} / ${textUnit}` : ''}}
    </div>
    <span class="checkmark"></span>
  </label>
</template>

<script>
    import { t } from '../../utils/i18n';

	export default {
		name: 'radio-button-item-deal',
		props: [
			'item',
			'value',
			'showShareArrow',
			'showPerUnitPrice',
			'showDiscount'
		],
		methods: {
			isBestseller() {
				return this.item.discountName.toLowerCase() === 'bestseller'
            },
			input(e) {
				return this.$emit('checkDeal', e.target.value)
			}
		},
        computed: {
          textUnit: () => t('checkout.unit'),
        },
        mounted(){
          this.$nextTick(function () {
            this.$emit('finish-render')
          })
        }
	}
</script>

<style lang="scss">
  .red {
    color:#e74c3c;
  }

  .label-container-radio__discount {
    color: #16a085;
  }

  .radio-button-deal {
    position: relative;
    margin: 5px 0;

    .label-container-radio__label {
      display: flex;

      .price {
        margin-left: auto;
      }
    }
  }

  .isChecked {
    background: #fef5eb
  }
</style>
