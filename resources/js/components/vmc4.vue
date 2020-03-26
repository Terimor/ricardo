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
                    class="lazy"
                    id="main-prod-image"
                    alt="Product image"
                    :data-src="productImage"
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
                    :vmc4Form="form"
                    :dealList="dealList"
                    :variantList="variantList"
                    :extraFields="extraFields"
                    :stateExtraField="stateExtraField"
                    :paymentMethodURL="paymentMethodURL"
                    @setPaymentMethodByCardNumber="setPaymentMethodByCardNumber">
                    <template slot="warranty">
                      <transition name="el-zoom-in-top">
                        <button
                          v-show="warrantyPriceText"
                          id="warranty-field-button"
                          @click="warrantyToggle">
                          <div class="label-container-checkbox">
                            <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
                            <span class="checkmark" :class="{ active: form.isWarrantyChecked }"></span>
                          </div>
                          <img class="lazy" :data-src="$root.cdn_url + '/assets/images/best-saller.png'" alt="">
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
                    class="lazy"
                    :data-src="imageSafePayment.url"
                    :alt="imageSafePayment.title"
                    :title="imageSafePayment.title">
                </div>
                <div class="text-content">
                  <p>
                    <img alt="" class="lazy" data-src="//static.saratrkr.com/images/lock.png">
                    <span v-html="textSafeSSLEncryption"></span>
                  </p>
                  <p><span v-html="textCreditCardInvoiced"></span><br/>"{{ companyDescriptorPrefix }}{{ billing_descriptor }}"</p>
                  <p v-if="$root.isAffIDEmpty" v-html="companyAddress"></p>
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
  import globals from '../mixins/globals';
  import RadioButtonItemDeal from "./common/RadioButtonItemDeal";
  import * as extraFields from '../mixins/extraFields';
	import { preparePurchaseData } from "../utils/checkout";
  import queryToComponent from '../mixins/queryToComponent';
  import purchasMixin from '../mixins/purchas';
  import blackFriday from '../mixins/blackFriday';
  import christmas from '../mixins/christmas';
  import { t, timage } from '../utils/i18n';
  import {fade} from "../utils/common";
  import {getRadioHtml} from '../utils/vmc4';


	export default {
		name: 'vmc4',
		components: {
      RadioButtonItemDeal,
    },
    mixins: [
      globals,
      purchasMixin,
      queryToComponent,
      extraFields.tplMixin,
      blackFriday,
      christmas,
    ],
		props: ['showPreloader', 'skusList'],
		data() {
			return {
				productImage: this.getProductImage(),
				form: {
          deal: null,
          isWarrantyChecked: false,
          variant: js_data.product.skus.length === 1 || !js_data.product.is_choice_required
            ? js_data.product.skus[0] && js_data.product.skus[0].code || null
            : null,
        },
			};
    },
    created() {
      if (this.queryParams['3ds'] && this.queryParams['3ds'] !== 'success') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
        }
        catch (err) {
          
        }
      }
    },
    mounted() {
      this.lazyload_update();
    },
    updated() {
      this.lazyload_update();
    },
		computed: {
      companyAddress() {
        return js_data.company_address.replace(' - ', '<br>');
      },
      companyDescriptorPrefix() {
        return this.$root.isAffIDEmpty ? js_data.company_descriptor_prefix : '';
      },
			productData() {
				return js_data.product;
			},
      billing_descriptor() {
        return js_data.product.billing_descriptor;
      },
      purchase() {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code) || null;

        return preparePurchaseData({
          purchaseList: this.productData.prices,
          product_name: this.productData.product_name,
          installments: this.form.installments,
          quantityToShow: [1, 3, 5],
          onlyDiscount: true,
          variant,
        });
      },
      dealList () {
        return this.purchase.map((it, idx) => ({
          class: `${this.form.deal === it.totalQuantity ? 'isChecked' : ''} ${it.discountName.toLowerCase() === 'bestseller' ? 'bestseller':''}`,
          value: it.totalQuantity,
          quantity: it.totalQuantity,
          label: getRadioHtml({
            ...it,
            installments: this.form.installments,
            idx
          })
        }))
      },
      variantList() {
        return this.skusList.map((it) => ({
          label: it.name,
          text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
          value: it.code,
          imageUrl: it.quantity_image[1]
        }));
      },
      warrantyPriceText() {
        const prices = js_data.product.prices;

        if (!this.form.deal) {
          return 0;
        }

        switch (this.form.installments) {
          case 1:
            return prices[this.form.deal].warranty_price_text;
          case 3:
            return prices[this.form.deal].installments3_warranty_price_text;
          case 6:
            return prices[this.form.deal].installments6_warranty_price_text;
        }

        return 0;
      },

      textSafeSSLEncryption: () => t('checkout.safe_sll_encryption'),
      textCreditCardInvoiced: () => t('checkout.credit_card_invoiced'),
      textWarranty: () => t('checkout.warranty'),

      imageSafePayment: () => timage('safe_payment'),
		},
		methods: {
      getProductImage() {
        const isInitial = !this.productImage;
        const quantity = /*this.form && +this.form.deal || */1;

        const skus = Array.isArray(js_data.product.skus)
          ? js_data.product.skus
          : [];

        const variant = (this.form && this.form.variant) || (skus[0] && skus[0].code) || null;
        const skuVariant = skus.find && skus.find(sku => variant === sku.code) || null;

        const productImage = js_data.product.image[+(js_query_params.image || null) - 1] || js_data.product.image[0];
        const skuImage = skuVariant && (skuVariant.quantity_image[quantity] || skuVariant.quantity_image[1]) || productImage;

        return isInitial ? productImage : skuImage;
      },
      animateProductImage() {
        const newProductImage = this.getProductImage();

        if (newProductImage !== this.productImage) {
          const imgPreload = new Image();
          imgPreload.src = newProductImage;

          fade('out', 300, document.querySelector('#main-prod-image'), true)
            .then(() => {
              this.productImage = newProductImage;
              setTimeout(() => fade('in', 300, document.querySelector('#main-prod-image'), true), 200);
            });
        }
      },
      warrantyToggle() {
        this.form.isWarrantyChecked = !this.form.isWarrantyChecked;
      },
		},
    watch: {
      'form.deal'(value) {
        window.selectedOffer = value ? 1 : 0;
        history.pushState({}, '', location.href);
      },
      'form.variant'() {
        this.animateProductImage();
      },
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
          margin: 15px 0 0;

          img {
            max-width: 350px;
          }
        }

        .text-content {
          padding: 8px 0;

          p {
            font-size: 13px;
            text-align: center;
            padding: 4px 0;

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

        .label-container-checkbox {
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
