<template>
  <label class="label-container-radio radio-button-deal"
          :class="[`item-${item.value}`, {disabled: item.isOutOfStock, 'labeled': item.discountName}]">
    <img class="share" :src="$root.cdn_url + '/assets/images/share.png'" v-if="showShareArrow">
    <input type="radio"
           :checked="item.value === value"
           name="radio"
           @change="input"
           :value="item.value"
           :disabled="item.isOutOfStock">
    <div class="label-container-radio__label">
      <div>
        <span class="label-container-radio__discount-name red">
          {{item.discountName}}
        </span>
        <span v-if="item.isOutOfStock" class="label-container-radio__soldout red">
           {{textSoldOut}}
        </span>
        <div>
          {{item.textComposite}}
        </div>
        <div class="label-container-radio__discount" v-html="item.discountText">
        </div>
      </div>
      <div class="price">
        <div class="bestseller" v-if="isBestseller()">
          <img :src="$root.cdn_url + '/assets/images/best-seller-checkout4.png'" alt="Bestseller">
        </div>
        <span>
          {{ price }}
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
      'quantityOfInstallments',
			'showShareArrow',
			'showPerUnitPrice',
			'showDiscount'
		],
		methods: {
			isBestseller() {
				return this.item.discountName.toLowerCase() === 'bestseller'
            },
			input(e) {
        if (window.navigator && navigator.userAgent && /Edge|Trident/.test(navigator.userAgent) && this.$parent.onInput) {
          this.$parent.onInput(e);
        }
			}
		},
    computed: {
      textUnit: () => t('checkout.unit'),
      textSoldOut: () => t('checkout.sold_out'),

      price() {
        return this.quantityOfInstallments + (this.item.newPrice || this.item.price);
      },
    },
    mounted() {
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

  .radio-button-deal {
    position: relative;
    margin: 5px 0;

    .label-container-radio__label {
      display: flex;

      .price {
        margin-left: auto;

        [dir="rtl"] & {
          margin-left: 0;
          margin-right: auto;
        }
      }
    }
  }
  .label-container-radio.disabled.labeled {
    .label-container-radio__soldout {
      &:before {
        display: inline-block;
        content: '-';
        text-decoration: inherit;
      }
    }
  }

  .isChecked {
    background: #fef5eb
  }
</style>
