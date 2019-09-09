<template>
  <div class="columns-content container vmc4">
    <div class="main-content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-5 col-md-5">
            <div class="column-with-head">
              <div class="col-content">
                <div class="product_img">
                  <img id="main-prod-image" alt="Product image"
                       :src="productImage">
                </div>
                <div class="product_desc">
                  <ul>
                    <li>High Sound Quality</li>
                    <li>Portable Charging</li>
                    <li>Ergonomic Design</li>
                    <li>iOs &amp; Android</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-7 col-md-7">
            <div class="column-with-head">
              <div class="col-content" id="form-steps">

                <payment-form-vmc4
                    :installments="form.installments"
                    :checkoutData="checkoutData"
                    :countryList="countryList"
                    @productImageChanged="setProductImage"
                    @setWarrantyPriceText="setWarrantyPriceText"
                    :cardNames="cardNames"
                    :list="dealList"
                    :variantList="variantList"
                    @onSubmit="submit">
                    <template slot="installment">
                      <select-field
                        v-if="withInstallments"
                        label="Please Select Amount Of Installments"
                        popperClass="emc1-popover-variant"
                        :list="installmentsList"
                        v-model="form.installments"
                        @input="getImplValue"
                      />
                    </template>
                    <template slot="warranty">
                      <transition name="el-zoom-in-top">
                        <button v-show="warrantyPriceText" id="warranty-field-button">
                          <label for="warranty-field" class="label-container-checkbox">
                            3 Years Additional Warranty On Your Purchase & Accessories: {{quantityOfInstallments}} {{warrantyPriceText}}
                            <input id="warranty-field" type="checkbox" v-model="form.isWarrantyChecked">
                            <span class="checkmark"></span>
                          </label>
                          <img src="/images/best-saller.png" alt="">
                          <i class="fa fa-arrow-right slide-right"></i>
                        </button>
                      </transition>
                    </template>
                </payment-form-vmc4>
              </div>
              <div class="secure-pay-content">
                <div class="logos-content">
                  <img src="/images/safe_payment_en.png" alt="safe payment">
                </div>
                <div class="text-content">
                  <p>
                    <img alt="" src="//static.saratrkr.com/images/lock.png">
                    Safe 256-Bit SSL encryption. <br>Your credit card will be invoiced as:
                    {{billing_descriptor}}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import RadioButtonItemDeal from "./common/RadioButtonItemDeal";
	import {preparePurchaseData} from "../utils/checkout";
  import {fade} from "../utils/common";
  import {getRadioHtml} from '../utils/vmc4';
  import printf from 'printf'

	export default {
		name: 'vmc4',
		components: {RadioButtonItemDeal},
		props: ['data'],
		data() {
			return {
        ImplValue: null,
        radioIdx: null,
        warrantyPriceText: null,
				billing_descriptor: checkoutData.product.billing_descriptor,
				productImage: null,
				countryList: [
					{
						value: 'US',
						text: 'USA',
						label: 'USA',
					}, {
						value: 'RU',
						text: 'Russia',
						label: 'Russia',
					}, {
						value: 'UA',
						text: 'Ukraine',
						label: 'Ukraine',
					}, {
						value: 'PT',
						text: 'Portugal',
						label: 'Portugal',
					}, {
						value: 'BR',
						text: 'Brazil',
						label: 'Brazil',
					}
				],
				cardNames: [
					{
						value: 'visa',
						text: 'VISA',
						label: 'VISA',
						imgUrl: '/images/cc-icons/visa.png'
					}, {
						value: 'mastercard',
						text: 'MasterCard',
						label: 'MasterCard',
						imgUrl: '/images/cc-icons/mastercard.png'
					}, {
						value: 'diners-club',
						text: 'DinnersClub',
						label: 'DinnersClub',
						imgUrl: '/images/cc-icons/diners-club.png'
					}, {
						value: 'discover',
						text: 'Discover',
						label: 'Discover',
						imgUrl: '/images/cc-icons/discover.png'
					}, {
						value: 'paypal',
						text: 'PayPal',
						label: 'PayPal',
						imgUrl: '/images/cc-icons/payPal.png'
					}
				],
				form: {
          isWarrantyChecked: false,
          installments: 1,
        },
				purchase: [],
        variantList: [],
        installmentsList: [
          {
            label: 'Pay full amount now',
            text: 'Pay full amount now',
            value: 1,
          }, {
            label: 'Pay in 3 installments',
            text: 'Pay in 3 installments',
            value: 3,
          }, {
            label: 'Pay in 6 installments',
            text: 'Pay in 6 installments',
            value: 6,
          }
        ]
			}
    },
    watch: {
      'form.installments' (val) {
          this.setPurchase({
            variant: this.form.variant,
            installments: val,
          })
      },
    },
		computed: {
      fullAmount () {
        return this.form.installments == 1;
      },
			checkoutData() {
				return checkoutData
			},
			productData() {
				return checkoutData.product
			},
      withInstallments () {
        return this.checkoutData.countryCode === 'BR' || this.checkoutData.countryCode === 'MX'
      },
      quantityOfInstallments () {
        const { installments } = this.form
        return installments && installments !== 1 ? installments + '× ' : ''
      },
      dealList () {
        return this.purchase.map((it, idx) => ({
          class: `${this.radioIdx === it.totalQuantity ? 'isChecked' : ''} ${it.discountName.toLowerCase() === 'bestseller' ? 'bestseller':''}`,
          value: it.totalQuantity,
          quantity: it.totalQuantity,
          label: getRadioHtml({
            ...it,
            installments: this.form.installments,
            text: printf(it.text, { color: this.form.variant }),
            idx
          })
        }))
      },
		},
		methods: {
			submit(val) {
				console.log(val)
      },
      getImplValue(value) {
        this.implValue = value;
        if (this.radioIdx) this.changeWarrantyValue();
      },
      setWarrantyPriceText(radioIdx) {
        this.radioIdx = Number(radioIdx);
        this.changeWarrantyValue();
      },
      changeWarrantyValue () {
        const prices = this.checkoutData.product.prices;
        this.implValue = this.implValue || 3;

        switch(this.implValue) {
          case 1:
            this.warrantyPriceText = prices[this.radioIdx].warranty_price_text;
            break;
          case 3:
            this.warrantyPriceText = prices[this.radioIdx].installments3_warranty_price_text;
            break;
          case 6:
            this.warrantyPriceText = prices[this.radioIdx].installments6_warranty_price_text;
            break;
          default:
            console.log('NOTHING');
            break;
        }
      },
			setCountryCodeByPhoneField(val) {
				if (val.iso2) {
					this.form.countryCodePhoneField = val.iso2.toUpperCase()
				}
			},
			setPurchase({variant, installments}) {
				this.purchase = preparePurchaseData({
					purchaseList: this.productData.prices,
					quantityToShow: [1, 3, 5],
					long_name: this.productData.skus[0].name,
					variant,
					installments,
          onlyDiscount: true
				})
			},
			setProductImage(val) {
				this.productImage = val
      }
		},
		mounted() {
			this.variantList = this.productData.skus.map((it) => ({
				label: it.name,
				text: `<div><img src="${it.images[0]}" alt=""><span>${it.name}</span></div>`,
				value: it.code,
				imageUrl: it.images[0]
			}));

			this.setPurchase({
				variant: this.form.variant,
				installments: 1,
      });
      
      if (this.withInstallments) {
        // set default installments
        this.form.installments =
          this.checkoutData.countryCode === 'BR' ? 3 :
          this.checkoutData.countryCode === 'MX' ? 1 :
          1
      }

			try {
				this.productImage = checkoutData.product.skus[0].images[0];
			} catch (_) {}
		}
	}
</script>
<style lang="scss">
  @import "../../sass/variables";

  .vmc4 {
    border-radius: 6px;
    box-shadow: 0 16px 24px 2px rgba(0, 0, 0, .14), 0 6px 30px 5px rgba(0, 0, 0, .12), 0 8px 10px -5px rgba(0, 0, 0, .2);
    background: #fff;
    margin-top: 30px;
    padding: 0;

    button:not(disabled) {
      cursor: pointer;
    }

    .main-content {
      .col-content {
        margin: 30px 0;
        position: relative;
        width: 100%;
        border-radius: 3px;
        color: rgba(0, 0, 0, .87);
        background: #fff;
        box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12);

        .product_img {
          padding: 10px;

          img {
            width: 85%;
            margin: 0 auto;
            display: block;
          }
        }

        .product_desc {
          padding: 10px;

          ul {
            color: #2c77cc;
            font-size: 15px;
            font-weight: 500;
            list-style: none;
            margin: 0;
            padding: 0;

            li {
              margin-top: 10px;
              position: relative;
              background: #ebebeb;
              padding: 10px 15px 10px 42px;
              border-radius: 30px;
              display: inline-block;
              margin-right: 8px;
              color: #0a0a0a;

              &:before {
                display: block;
                content: '✓';
                position: absolute;
                left: 14px;
                top: 7px;
                font-size: 22px;
                color: #25aae1;
              }
            }
          }
        }
      }
      .secure-pay-content {
        text-align: center;

        .logos-content {
          margin: 15px 0 20px;
          img {
            max-width: 350px;
          }
        }

        .text-content {
          p {
            text-align: center;
            margin-bottom: 10px;

            img {
              max-width: 100%;
            }
          }
        }
      }
    }

    .radio-button-group {
      .label-container-radio__label {
        font-size: 16px;
      }

      .label-container-radio__name-price {
        display: flex;
        justify-content: space-between;
      }

      .label-container-radio.with-discount .label-container-radio__name-price > span:nth-child(2) {
        text-decoration: line-through;
      }
    }

    .vmc4__deal {
      h3 {
        padding-left: 20px;
      }

      #warranty-field-button {
        width: 100%;
        position: relative;
        height: 95px;
        margin-top: 22px;
        background-color: rgba(216, 216, 216, .71);
        border-radius: 5px;
        border: 1px solid rgba(0, 0, 0, 0.4);
        outline: none;

        &:hover {
          background-color: rgba(191,191,191,.71);
          background-image: linear-gradient(to bottom, #e6e6e6 0, #ccc 100%);
        }

        label[for=warranty-field] {
          font-weight: bold;
          line-height: 1.8;
          text-align: left;
          text-transform: capitalize;
          font-size: 16px;
          padding: 23px 70px 30px;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;

          .checkmark {
            top: 20px;
            left: 40px;
          }
        }

        input[type=checkbox] {
          position: absolute;
          top: 23px;
          left: 45px;
        }

        & > img {
          position: absolute;
          width: 30px;
          height: auto;
          top: -7px;
          right: -7px;
        }

        & > .fa-arrow-right {
          position: absolute;
          font-size: 18px;
          color: #dc003a;
          top: 20px;
          left: 10px;
          animation: slide-right .5s cubic-bezier(.25,.46,.45,.94) infinite alternate both;
        }
      }
    }

    #warranty-field-button {
      width: 100%;
      position: relative;
      height: 95px;
      margin-top: 22px;
      background-color: rgba(216, 216, 216, .71);
      border-radius: 5px;
      border: 1px solid rgba(0, 0, 0, 0.4);
      outline: none;

      &:hover {
        background-color: rgba(191,191,191,.71);
        background-image: linear-gradient(to bottom, #e6e6e6 0, #ccc 100%);
      }

      label[for=warranty-field] {
        font-weight: bold;
        line-height: 1.8;
        text-align: left;
        text-transform: capitalize;
        font-size: 16px;
        padding: 23px 70px 30px;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        .checkmark {
          top: 20px;
          left: 40px;
        }
      }

      input[type=checkbox] {
        position: absolute;
        top: 23px;
        left: 45px;
      }

      & > img {
        position: absolute;
        width: 30px;
        height: auto;
        top: -7px;
        right: -7px;
      }

      & > .fa-arrow-right {
        position: absolute;
        font-size: 18px;
        color: #dc003a;
        top: 20px;
        left: 10px;
        animation: slide-right .5s cubic-bezier(.25,.46,.45,.94) infinite alternate both;
      }
    }
  }

  @media screen and (min-width: 1200px) {
    .vmc4 {
      max-width: 1170px;
    }
  }

  @media screen and (max-width: 992px) {
    .container {
      max-width: 100%;
    }
  }

  @media screen and ($s-down) {
    header {
      margin-top: 25px;
    }
  }

</style>