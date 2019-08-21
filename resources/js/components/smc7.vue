<template>
  <div v-if="$v">
    <div class="container smc7">
      <div class="row">
        <div class="paper col-12  smc7__product">
          <div class="col-md-7 image-wrapper">
            <img id="product-image-head" :src="productImage" alt="">
          </div>
          <div class="col-md-5 advantages">
            <ul>
              <li class="advantage"><i class="fa fa-check"></i>High Sound Quality</li>
              <li class="advantage"><i class="fa fa-check"></i>Portable Charging</li>
              <li class="advantage"><i class="fa fa-check"></i>Ergonomic Design</li>
              <li class="advantage"><i class="fa fa-check"></i>iOs & Android</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="container offer">
          <p><span class="bold">Special Offer:</span> {{productData.long_name}}</p>
          <p>Price:&nbsp;<span id="old-price"
                               class="price-object productprice-old-object strike">₴3,598</span>
            <span class="price-span">
                <b><span id="new-price" class="price-object productprice-object"> ₴1,799</span></b>
              </span>&nbsp;(50% discount per unit)
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-7">
          <div class="paper smc7__deal">
            <div class="d-flex">
              <div class="smc7__sale">
                <div class="sale-badge dynamic-sale-badge ">
                  <div class="dynamic-sale-badge__background"></div>
                  <div class="dynamic-sale-badge__container">
                    <span class="badge-discount-percentage">50%</span> Off
                  </div>
                </div>
              </div>
              <p class="smc7__deal__text">
                <strong>Free Shipping</strong> on all orders <strong>Today!</strong>
                Do not browse away from this page! <strong>Free delivery available today</strong>
              </p>
            </div>

            <div class="smc7__step-1">
              <h2>Step 1: Choose your Deal</h2>
              <div class="smc7__step-1__titles">
                <h3>Article</h3>
                <h3>Price</h3>
              </div>

              <span class="error" v-show="$v.form.deal.$dirty && $v.form.deal.$invalid">Please select a product promotion.</span>

              <radio-button-group
                  :withCustomLabels="true"
                  v-model="form.deal"
              >
                <radio-button-item-deal
                    :value="form.deal"
                    v-for="item in purchase"
                    :item="{
                      ...item,
                      value: item.totalQuantity,
                    }"
                    :key="item.value"/>
              </radio-button-group>
            </div>

            <div class="smc7__step-2">
              <h2>Step 2: Please select your variant</h2>
              <select-field
                  popperClass="smc7-popover-variant"
                  v-model="form.variant"
                  :config="{
                    prefix: 'EchoBeat7'
                  }"
                  :rest="{
                    placeholder: 'Variant'
                  }"
                  :list="variantList"/>
            </div>

            <div class="smc7__step-3">
              <h2>Step 3: Contact Information</h2>
              <div class="full-name">
                <text-field
                    :validation="$v.form.fname"
                    validationMessage="Please enter your first name"
                    theme="variant-1"
                    label="First Name"
                    class="first-name"
                    :rest="{
                      placeholder: 'First Name',
                      autocomplete: 'given-name'
                    }"
                    v-model="form.fname"/>
                <text-field
                    :validation="$v.form.lname"
                    validationMessage="Please enter your last name"
                    theme="variant-1"
                    label="Last Name"
                    class="last-name"
                    :rest="{
                      placeholder: 'Last Name',
                      autocomplete: 'family-name'
                    }"
                    v-model="form.lname"/>
              </div>
              <text-field
                  :validation="$v.form.email"
                  validationMessage="Please enter a valid e-mail"
                  theme="variant-1"
                  label="Your Email Address"
                  :rest="{
                    placeholder: 'Your Email Address',
                    autocomplete: 'email'
                  }"
                  v-model="form.email"/>
              <phone-field
                  :validation="$v.form.phone"
                  validationMessage="Please enter a valid phone number"
                  :countryCode="form.countryCodePhoneField"
                  theme="variant-1"
                  label="Your Phone Number"
                  :rest="{
                    autocomplete: 'off'
                  }"
                  v-model="form.phone"/>
            </div>
          </div>
        </div>
        <div class="paper col-md-5 smc7__step-4">
          <div class="d-flex">
            <div class="smc7__step-4__product">
              <h2>EchoBeat i7</h2>
              <p>GET 50% OFF TODAY + FREE SHIPPING</p>
            </div>
            <img id="product-image-body" :src="productImage" alt="Product image">
          </div>
          <h2 class="step-title">Step 4: Contact Information</h2>
          <payment-form-smc7
              :countryList="countryList"
              :cardNames="cardNames"
              :$v="$v"
              :paymentForm="form"/>
          <button
              @click="submit"
              id="purchase-button"
              type="button"
              class="green-button-animated">
            <span class="purchase-button-text">YES! SEND ME MY PURCHASE WITH FREE SHIPPING NOW</span>
          </button>
          <div class="smc7__bottom">
            <img src="/images/safe_payment_en.png" alt="safe payment">
            <p><i class="fa fa-lock"></i>Safe 256-Bit SSL encryption.</p>
            <p>Your credit card will be invoiced as: "MDL*EchoBeat"</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
	import {preparePurchaseData} from "../utils/checkout";
	import {getRadioHtml} from "../utils/emc1";
	import printf from "printf";
	import RadioButtonItemDeal from "./common/RadioButtonItemDeal";
	import smc7validation from "../validation/smc7-validation";

	export default {
		name: 'smc7',
		components: {RadioButtonItemDeal},
    validations: smc7validation,
		props: ['showPreloader'],
		data() {
			return {
				productImage: '/images/headphones-white.png',
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
					}, {
						value: 'mastercard',
						text: 'MasterCard',
						label: 'MasterCard',
					}, {
						value: 'diners-club',
						text: 'DinnersClub',
						label: 'DinnersClub',
					}, {
						value: 'discover',
						text: 'Discover',
						label: 'Discover',
					}, {
						value: 'PayPal',
						text: 'PayPal',
						label: 'PayPal',
					}
				],
				form: {
					countryCodePhoneField: checkoutData.countryCode,
					deal: null,
					fname: null,
					lname: null,
					email: null,
					phone: null,
					variant: 'sku1',
					country: checkoutData.countryCode,
					streetAndNumber: null,
					city: null,
					state: null,
					zipCode: null,
					paymentType: 'VISA',
					cardNumber: '',
					month: null,
					year: null,
					cvv: null,
					cardType: 'credit',
				},
				purchase: [],
				variantList: []
			}
		},
		computed: {
			checkoutData() {
				return checkoutData
			},
			productData() {
				return checkoutData.product
			},
			dealList() {
				return this.purchase.map((it, idx) => ({
					value: idx + 1,
					label: getRadioHtml({
						...it,
						installments: 1,
						text: printf(it.text, {color: this.form.variant}),
						idx,
						shareButton: 0
					})
				}))
			}
		},
		methods: {
			submit() {
				this.$v.form.$touch();
			},
			setPurchase({variant, installments}) {
				this.purchase = preparePurchaseData({
					purchaseList: this.productData.prices,
          quantityToShow: [1,2,3,4,5],
					long_name: this.productData.long_name,
					variant,
					installments,
				})
			}
		},
		mounted() {
			this.variantList = this.productData.skus.map((it) => ({
				label: it.name,
				text: `<div><img src="/images/headphones-white.png" alt=""><span>${it.name}</span></div>`,
				value: it.code,
				imageUrl: '/images/headphones-white.png'
			}));

			this.setPurchase({
				variant: this.form.variant,
				installments: 1,
			})
		}
	}
</script>

<style lang="scss">
  .smc7.container {
    max-width: 970px;
  }

  .smc7 {
    &__product {
      display: flex;

      .image-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;

        img {
          height: 217px;
        }
      }

      .advantages {
        display: flex;
        align-items: center;

        ul {
          width: 100%;
          list-style: none;
          padding: 0;
        }

        .advantage {
          background: #e3e3e3;
          margin: 3px 0;
          padding: 15px;
          font-weight: 700;

          i {
            color: green;
            margin-right: 5px;
          }
        }
      }
    }

    .offer {
      padding: 20px 0;
    }

    &__deal {
      &__text {
        font-size: 18px;
        font-style: italic;
        padding-left: 20px;
        margin: 0;
      }
    }

    &__step-1 {
      &__titles {
        display: flex;
        padding: 0 20px;

        h3:first-child {
          width: 60%;
        }

        h3:last-child {
          width: 40%;
          text-align: right;
        }
      }

      .radio-button-group .label-container-radio__label {
        font-weight: bold;
        font-size: 16px;
      }

      .radio-button-group .label-container-radio__name-price {
        display: flex;
        justify-content: space-between;
      }

      .radio-button-group .label-container-radio__best-seller {
        display: flex;
        justify-content: space-between;
        color: #e74c3c;
      }

      .radio-button-group {
        .label-container-radio {
          &:nth-child(1) {
            background: #fef036;
          }

          &:hover {
            background: #fef9ae;
          }
        }

      }

      .share {
        position: absolute;
        transform: rotate3d(-10, -3, 0, 180deg);
        height: auto;
        width: 40px;
        top: -17px;
        left: -30px;
      }
    }

    &__step-3 {
      .full-name {
        display: flex;

        .first-name {
          width: 40%;
          margin-right: 10px;
        }

        .last-name {
          width: calc(60% - 11px);
        }
      }
    }

    &__step-3 {
      .input-container {
        margin-bottom: 15px;
      }

      .input-container.variant-1 input,
      .phone-input-container.variant-1 input {
        background-color: #ffffff;
        font-size: 14px;
        border-radius: unset;
        border: 1px solid #000000;
      }

    }

    &__step-4 {
      .step-title {
        margin-top: 60px;
      }

      .select {
        margin-bottom: 15px;

        .el-select {
          outline: 1px inset #000;
        }
      }

      .card-date {
        display: flex;
        flex-wrap: wrap;
        width: 70%;
        padding-right: 30px;
        margin-bottom: 10px;
      }

      .card-date > .label {
        width: 100%;
        margin-bottom: 6px;
      }

      .card-date > div {
        width: calc(40% - 5px);
        margin-right: 10px;
      }

      .select.variant-1 input {
        background-color: #ffffff;
        font-size: 17px;
      }

      .cvv-field {
        width: calc(30%);
      }

      .el-select-dropdown__item {
        font-size: 17px;
        font-weight: 700;
      }

      .input-container.variant-1 input {
        background-color: #ffffff;
        border-radius: unset;
        border: 1px solid #000000;
      }

      .input-container {
        margin-bottom: 15px;
      }

      #product-image-body {
        max-height: 100px;
      }
    }

    &__bottom {
      display: flex;
      flex-direction: column;

      img {
        width: 80%;
        margin: 0 auto;
      }

      p {
        text-align: center;
        font-size: 13px;
      }

      p i {
        position: relative;
        margin-right: 4px;
        top: 2px;
        font-size: 18px;
        color: #409EFF;
      }
    }
  }

  .smc7-popover-variant {
    .el-select-dropdown__item {
      height: auto;
    }

    .select__label {
      opacity: 1;

      & > div {
        display: flex;
        align-items: center;

        img {
          height: 80px;
          width: auto;
          margin-right: 25px;
        }

        span {
          font-size: 17px;
          font-weight: 700;
        }
      }
    }
  }
</style>
