<template>
  <div v-if="!showPreloader" class="columns-content container vmc4">
    <div class="main-content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-5 col-md-5">
            <div class="column-with-head">
              <div class="col-content" id="col-prod-image">
                <div class="product_img">
                  <img
                    id="main-prod-image"
                    alt="Product image"
                    :src="productImage"
                  >
                </div>
                <div class="product_desc" v-html="productData.description"></div>
              </div>
            </div>
          </div>
          <div class="col-sm-7 col-md-7">
            <div class="column-with-head">
              <div class="col-content" id="form-steps">
                <payment-form-vmc4
                    :productImage="productImage"
                    :installments="form.installments"
                    :isWarrantyChecked="form.isWarrantyChecked"
                    :checkoutData="checkoutData"
                    :countryList="countryList"
                    @productImageChanged="setProductImage"
                    @setWarrantyPriceText="setWarrantyPriceText"
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
                            <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
                            <input id="warranty-field" type="checkbox" v-model="form.isWarrantyChecked">
                            <span class="checkmark"></span>
                          </label>
                          <img :src="$root.cdnUrl + '/assets/images/best-saller.png'" alt="">
                          <i class="fa fa-arrow-left slide-left"></i>
                          <i class="fa fa-arrow-right slide-right"></i>
                        </button>
                      </transition>
                    </template>
                </payment-form-vmc4>
              </div>
              <div class="secure-pay-content">
                <div class="logos-content">
                  <img
                    :src="imageSafePayment.url"
                    :alt="imageSafePayment.title"
                    :title="imageSafePayment.title">
                </div>
                <div class="text-content">
                  <p>
                    <img alt="" src="//static.saratrkr.com/images/lock.png">
                    <span v-html="textSafeSSLEncryption"></span><br><span v-html="textCreditCardInvoiced"></span> {{billing_descriptor}}
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
	import { preparePurchaseData } from "../utils/checkout";
  import queryToComponent from '../mixins/queryToComponent';
  import { t, timage } from '../utils/i18n';
  import {fade} from "../utils/common";
  import {getRadioHtml} from '../utils/vmc4';

	export default {
		name: 'vmc4',
		components: {
      RadioButtonItemDeal,
    },
    mixins: [
      queryToComponent,
    ],
		props: ['data', 'showPreloader'],
		data() {
			return {
        ImplValue: null,
        radioIdx: null,
        warrantyPriceText: null,
				billing_descriptor: checkoutData.product.billing_descriptor,
				productImage: null,
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
    created() {
      if (this.queryParams['3ds'] === 'failure') {
        const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData'));

        if (selectedProductData) {
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.installments = selectedProductData.installments || this.form.installments;
        }
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
        return this.checkoutData.countryCode === 'br'
          || this.checkoutData.countryCode === 'mx'
          || this.checkoutData.countryCode === 'co'
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
            idx
          })
        }))
      },
      countryList () {
        return checkoutData.countries.map(name => ({
          value: name,
          text: t('country.' + name),
          label: t('country.' + name),
        }));
      },
      textSafeSSLEncryption: () => t('checkout.safe_sll_encryption'),
      textCreditCardInvoiced: () => t('checkout.credit_card_invoiced'),
      textWarranty: () => t('checkout.warranty'),

      imageSafePayment: () => timage('safe_payment'),
		},
		methods: {
			submit(form, ipqsResult) {

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
            this.warrantyPriceText = prices[this.radioIdx].warranty_price_text;
            break;
          case 6:
            this.warrantyPriceText = prices[this.radioIdx].warranty_price_text;
            break;
          default:
            break;
        }
      },
			setCountryCodeByPhoneField(val) {
				if (val.iso2) {
					this.form.countryCodePhoneField = val.iso2;
				}
			},
			setPurchase({variant, installments}) {
				this.purchase = preparePurchaseData({
					purchaseList: this.productData.prices,
					quantityToShow: [1, 3, 5],
					product_name: this.productData.product_name,
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
				text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
				value: it.code,
				imageUrl: it.quantity_image[1]
			}));

			this.setPurchase({
				variant: this.form.variant,
				installments: 1,
      });

      if (this.withInstallments) {
        // set default installments
        this.form.installments =
          this.checkoutData.countryCode === 'br' ? 3 :
          this.checkoutData.countryCode === 'mx' ? 1 :
          1
      }
		},
	}
</script>
<style lang="scss">
  @import "../../sass/variables";

  .vmc4 {
    border-radius: 6px;
    box-shadow: 0 16px 24px 2px rgba(0, 0, 0, .14), 0 6px 30px 5px rgba(0, 0, 0, .12), 0 8px 10px -5px rgba(0, 0, 0, .2);
    background: #fff;
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

        @media screen and (max-width: 575px) {
          &#col-prod-image {
            margin-bottom: 8px;
          }

          &#form-steps {
            margin-top: 8px;
          }
        }

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
          display: flex;
          justify-content: center;
          margin: 15px 0 20px;

          img {
            max-width: 350px;
          }
        }

        .text-content {
          p {
            font-size: 13px;
            text-align: center;
            margin-bottom: 10px;

            img {
              max-width: 12px;
              position: relative;
              top: 2px;
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
        margin-top: 22px;
        margin-bottom: 22px;
        background-color: rgba(216, 216, 216, .71);
        border-radius: 5px;
        border: 1px solid rgba(0, 0, 0, 0.4);
        outline: none;

        &:hover {
          background-color: rgba(191,191,191,.71);
          background-image: linear-gradient(to bottom, #e6e6e6 0, #ccc 100%);
        }

        label[for=warranty-field] {
          margin: 0;
          padding: 17px 70px 30px;
          text-align: left;
          font-size: 16px;
          line-height: 1.8;
          font-weight: bold;
          text-transform: capitalize;

          [dir="rtl"] & {
            text-align: right;
          }

          .checkmark {
            top: 20px;
            left: 40px;

            [dir="rtl"] & {
              left: auto;
              right: 40px;
            }
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

          [dir="rtl"] & {
            left: -7px;
            right: auto;
            transform: rotate(-24deg);
          }
        }

        & > .fa-arrow-left {
          display: none;
          position: absolute;
          font-size: 18px;
          color: #dc003a;
          top: 20px;
          right: 10px;

          [dir="rtl"] & {
            display: block;
          }
        }

        & > .fa-arrow-right {
          position: absolute;
          font-size: 18px;
          color: #dc003a;
          top: 20px;
          left: 10px;

          [dir="rtl"] & {
            display: none;
          }
        }
      }
    }

  }

  .tpl-vmc4 {

    main.pt-4 {
      padding-top: 0!important;
    }

    #header {
      background-color: transparent;
      box-shadow: none;
    }

    .footer {
      background-color: transparent;
      padding: 35px 15px 60px;
    }

    .footer__row {
      justify-content: center;
    }

    .footer__row-item {
      margin: 0;

      &:before {
        content: '\007c';
        color: #6c6c6c;
        padding: 5px 7px;
      }

      &:first-child:before {
        display: none;
      }
    }

    .footer__link {
      color: #337ab7;
      font-size: 13px;
      font-weight: 400;
      text-decoration: none;
      text-transform: capitalize;
    }

    @media screen and (max-width: 480px) {
      .footer__row {
        flex-direction: column;
      }

      .footer__row-item {
        margin-bottom: 8px;

        &:before {
          display: none;
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

  }
</style>
