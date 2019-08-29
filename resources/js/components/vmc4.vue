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
                    :checkoutData="checkoutData"
                    :countryList="countryList"
                    @productImageChanged="setProductImage"
                    :cardNames="cardNames"
                    :list="purchase"
                    :variantList="variantList"
                    @onSubmit="submit"/>
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

	export default {
		name: 'vmc4',
		components: {RadioButtonItemDeal},
		props: ['data'],
		data() {
			return {
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
				form: {},
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

		},
		methods: {
			submit(val) {
				console.log(val)
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