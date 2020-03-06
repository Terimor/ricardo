<template>
    <div v-if="$v && !showPreloader">
        <div class="container">
            <ProductOffer />
        </div>
        <div class="container main">
            <div class="row">
                <div class="col-md-7">
                    <div class="paper main__deal">
                        <div class="d-flex">
                            <div class="main__sale">
                                <SaleBadge />
                            </div>
                            <p class="main__deal__text" v-html="textMainDealText"></p>
                        </div>
                        <h2 class="step1-title"><span v-html="textStep"></span> 1: <span v-html="textChooseDeal"></span></h2>

                        <Installments
                          popperClass="emc1-popover-variant"
                          :extraFields="extraFields"
                          :form="form" />

                        <div class="step1-titles">
                          <h3 v-html="textArtcile"></h3>
                          <h3 v-html="textPrice"></h3>
                        </div>

                        <span class="error" v-show="$v.form.deal.$dirty && $v.form.deal.$invalid" v-html="textMainDealError"></span>

                        <radio-button-group
                          v-model="form.deal"
                          :validation="$v.form.deal"
                          :list="dealList" />

                        <div v-if="isShowVariant" class="variant-selection">
                          <h2><span v-html="textStep"></span> 2: <span v-html="textSelectVariant"></span></h2>

                          <Variant
                            :$v="$v.form.variant"
                            :form="form"
                            name="variant"
                            @change="onVariantChange" />
                        </div>

                        <Warranty
                          :form="form" />

                        <div
                          v-if="$root.isAffIDEmpty && form.deal"
                          class="price-total">
                          <div class="label">{{ textTotalAmount }}</div>
                          <div class="value">{{ xprice_total_text }}</div>
                        </div>
                    </div>
                </div>
                <div class="paper col-md-5 main__payment">
                    <img id="product-image" class="lazy" :data-src="productImage" alt="">
                    <template v-if="!isPurchasAlreadyExists">
                        <h2><span v-html="textStep"></span> {{ getStepOrder(3) }}: <span v-html="textPaymentMethod"></span></h2>
                        <h3 v-html="textPaySecurely"></h3>
                        <payment-provider-radio-list
                          v-model="form.paymentProvider"
                          @input="activateForm" />
                        <paypal-button
                          v-if="$root.paypalEnabled"
                          :createOrder="paypalCreateOrder"
                          :onApprove="paypalOnApprove"
                          v-show="form.installments === 1"
                          :$vvariant="$v.form.variant"
                          :$vdeal="$v.form.deal"
                          @click="paypalSubmit"
                        >{{ paypalRiskFree }}</paypal-button>
                        <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
                        <transition name="el-zoom-in-top">
                            <payment-form
                              :firstTitle="`${textStep} ${getStepOrder(4)}: ${textContactInformation}`"
                              :secondTitle="`${textStep} ${getStepOrder(5)}: ${textDeliveryAddress}`"
                              :thirdTitle="`${textStep} ${getStepOrder(6)}: ${textPaymentDetails}`"
                              v-if="form.paymentProvider && isFormShown"
                              :$v="$v"
                              :paymentForm="form"
                              :extraFields="extraFields"
                              :stateExtraField="stateExtraField"
                              :paymentMethodURL="paymentMethodURL"
                              @setPaymentMethodByCardNumber="setPaymentMethodByCardNumber"
                              @setPromotionalModal="setPromotionalModal" />
                        </transition>
                        <div class="main__bottom">
                            <img
                              class="lazy"
                              :data-src="imageSafePayment.url"
                              :alt="imageSafePayment.title"
                              :title="imageSafePayment.title">
                            <p><i class="fa fa-lock"></i><span v-html="textSafeSSLEncryption"></span></p>
                            <p><span v-html="textCreditCardInvoiced"></span><br/>"{{ companyDescriptorPrefix }}{{ productData.billing_descriptor }}"</p>
                            <p v-if="$root.isAffIDEmpty" v-html="companyAddress"></p>
                        </div>
                    </template>
                    <PurchasAlreadyExists
                            v-else
                    />
                </div>
            </div>
        </div>

        <el-dialog
                @click="isOpenPromotionModal = false"
                class="cvv-popup"
                :title="promo_modal_title"
                :lock-scroll="false"
                :visible.sync="isOpenPromotionModal">
            <div class="cvv-popup__content">
                <p class="error-container" v-html="promo_modal_text"></p>

                <button
                        @click="isOpenPromotionModal = false"
                        style="height: 67px; margin: 0"
                        type="button"
                        class="green-button-animated">
                    <span class="purchase-button-text" v-html="textMainDealErrorPopupButton"></span>
                </button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
  import emc1Validation from '../validation/emc1-validation';
  import scrollToError from '../mixins/formScrollToError';
  import globals from '../mixins/globals';
  import notification from '../mixins/notification';
  import * as extraFields from '../mixins/extraFields';
  import queryToComponent from '../mixins/queryToComponent'
  import blackFriday from '../mixins/blackFriday';
  import christmas from '../mixins/christmas';
  import { t, timage } from '../utils/i18n';
  import { getNotice, getRadioHtml } from '../utils/emc1';
  import ProductItem from './common/ProductItem';
  import Variant from './common/common-fields/Variant';
  import Warranty from './common/Warranty';
  import SaleBadge from './common/SaleBadge';
  import ProductOffer from '../components/common/ProductOffer';
  import PurchasAlreadyExists from './common/PurchasAlreadyExists';
  import Installments from './common/extra-fields/Installments';
  import { fade } from '../utils/common';
  import { preparePurchaseData } from '../utils/checkout';
  import purchasMixin from '../mixins/purchas';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
  import { ipqsCheck } from '../services/ipqs';
  import { queryParams } from  '../utils/queryParams';
  import logger from '../mixins/logger';


  export default {
    name: 'emc1',
    mixins: [
      globals,
      notification,
      scrollToError,
      queryToComponent,
      extraFields.tplMixin,
      purchasMixin,
      blackFriday,
      christmas,
      logger,
    ],
    components: {
      SaleBadge,
      ProductItem,
      Variant,
      Warranty,
      ProductOffer,
      PurchasAlreadyExists,
      Installments,
    },
    props: ['showPreloader', 'skusList'],
    data () {
      return {
        isFormShown: false,
        paypalPaymentError: '',
        selectedProductData: {
          prices: null,
          quantity: null,
        },
        form: {
          isWarrantyChecked: false,
          countryCodePhoneField: js_data.country_code,
          deal: null,
          variant: js_data.product.skus.length === 1 || !js_data.product.is_choice_required
            ? js_data.product.skus[0] && js_data.product.skus[0].code || null
            : null,
          paymentProvider: null,
          fname: null,
          lname: null,
          email: null,
          phone: null,
          street: null,
          city: null,
          zipcode: null,
          country: js_data.countries.indexOf(js_data.country_code) !== -1
            ? js_data.country_code
            : null,
          cardHolder: null,
          cardNumber: null,
          cardDate: null,
          cvv: null,
          terms: null,
        },
        isOpenPromotionModal: false,
        productImage: this.getProductImage(),
        disableAnimation: true,
      }
    },
    created() {
      const openForm = +js_query_params.openform;

      if (openForm !== 0 && (!this.$root.paypalEnabled || openForm === 1)) {
        this.form.paymentProvider = 'credit-card';
        this.isFormShown = true;
      }

      if (this.queryParams['3ds'] && this.queryParams['3ds'] !== 'success') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};

          this.form.deal = selectedProductData.deal || this.form.deal;
          this.form.variant = selectedProductData.variant || this.form.variant;
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.paymentProvider = selectedProductData.paymentProvider || this.form.paymentProvider;
          this.form.fname = selectedProductData.fname || this.form.fname;
          this.form.lname = selectedProductData.lname || this.form.lname;
          this.form.email = selectedProductData.email || this.form.email;
          this.form.phone = selectedProductData.phone || this.form.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.street = selectedProductData.street || this.form.street;
          this.form.city = selectedProductData.city || this.form.city;
          this.form.zipcode = selectedProductData.zipcode || this.form.zipcode;
          this.form.country = selectedProductData.country || this.form.country;
          this.isFormShown = true;
        }
        catch (err) {
          
        }
      }

      setTimeout(() => {
        this.disableAnimation = false;
      }, 1000);
    },
    computed: {
      companyAddress() {
        return js_data.company_address.replace(' - ', '<br>');
      },
      companyDescriptorPrefix() {
        return this.$root.isAffIDEmpty ? js_data.company_descriptor_prefix : '';
      },
      isShowVariant() {
        return this.variantList.length > 1 && (!js_query_params.variant || js_query_params.variant === '0');
      },
      productData () {
        return js_data.product
      },
      dealList () {
        const isSellOutArray = queryParams().sellout
          ? queryParams().sellout.split(',')
          : [];

        return this.purchase.map((it, idx) => ({
          value: it.totalQuantity,
          quantity: it.totalQuantity,
          isOutOfStock: isSellOutArray.indexOf(String(it.totalQuantity)) !== -1,
          isLabeled: !!it.discountName,
          label: getRadioHtml({
            ...it,
            installments: this.form.installments,
            idx,
          })
        }))
      },
      productImagesList() {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code) || null;

        const skus = Array.isArray(js_data.product.skus)
          ? js_data.product.skus
          : [];

        const product = skus.find(sku => variant === sku.code);

        return Object.values(product.quantity_image);
      },
      purchase() {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code) || null;

        return preparePurchaseData({
          purchaseList: this.productData.prices,
          product_name: this.productData.product_name,
          installments: this.form.installments,
          variant,
        });
      },
      variantList() {
        return this.skusList.map((it) => ({
          label: it.name,
          text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
          value: it.code,
          imageUrl: it.quantity_image[1],
        }));
      },
      price_total_text() {
        let result = '';

        if (this.form.deal) {
          switch (this.form.installments) {
            case 1:
              result = this.form.isWarrantyChecked
                ? js_data.product.prices[this.form.deal].total_amount_text
                : js_data.product.prices[this.form.deal].value_text;
              break;
            case 3:
              result = this.form.isWarrantyChecked
                ? js_data.product.prices[this.form.deal].installments3_total_amount_text
                : js_data.product.prices[this.form.deal].installments3_value_text;
              break;
            case 6:
              result = this.form.isWarrantyChecked
                ? js_data.product.prices[this.form.deal].installments6_total_amount_text
                : js_data.product.prices[this.form.deal].installments6_value_text;
              break;
          }
        }

        return result;
      },
      price_multiplier() {
        return this.form.installments !== 1
          ? this.form.installments + '× '
          : '';
      },
      xprice_total_text() {
        return this.price_multiplier + this.price_total_text;
      },
      textMainDealText: () => t('checkout.main_deal.message', { country: t('country.' + js_data.country_code) || '' }),
      textStep: () => t('checkout.step'),
      textChooseDeal: () => t('checkout.choose_deal'),
      textArtcile: () => t('checkout.article'),
      textPrice: () => t('checkout.header_banner.price'),
      textMainDealError: () => t('checkout.main_deal.error'),
      textMainDealErrorPopupButton: () => t('checkout.main_deal.error_popup.button'),
      textSelectVariant: () => t('checkout.select_variant'),
      textPaymentMethod: () => t('checkout.payment_method'),
      textPaySecurely: () => t('checkout.pay_securely'),
      textSafeSSLEncryption: () => t('checkout.safe_sll_encryption'),
      textCreditCardInvoiced: () => t('checkout.credit_card_invoiced'),
      textContactInformation: () => t('checkout.contact_information'),
      textDeliveryAddress: () => t('checkout.delivery_address'),
      textPaymentDetails: () => t('checkout.payment_details'),
      textSpecialOfferPopupTitle: () => t('checkout.special_offer_popup.title'),
      textSpecialOfferPopupMessage: () => t('checkout.special_offer_popup.message'),
      textSpecialOfferPopupButtonPurchase: () => t('checkout.special_offer_popup.button_purchase'),
      textSpecialOfferPopupButtonEmpty: () => t('checkout.special_offer_popup.button_empty'),
      paypalRiskFree: () => t('checkout.paypal.risk_free'),
      textTotalAmount: () => t('checkout.total_amount'),
      imageSafePayment: () => timage('safe_payment'),

      promo_modal_title() {
        if (this.$v.form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.title');
        } else if (this.$v.form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },
      promo_modal_text() {
        if (this.$v.form.deal.$invalid) {
          return t('checkout.main_deal.error_popup.message');
        } else if (this.$v.form.variant.$invalid) {
          return t('checkout.select_variant');
        }
      },
    },
    watch: {
      'form.deal'(value) {
        window.selectedOffer = value ? 1 : 0;
        history.pushState({}, '', location.href);
      },
      'form.paymentProvider'(value) {
        window.selectedPayment = value;
        history.pushState({}, '', location.href);
      },
      purchase() {
        this.refreshTopBlock();
      },
    },
    validations: emc1Validation,
    methods: {
      onVariantChange() {
        this.animateProductImage();
      },
      activateForm() {
        this.isFormShown = true;

        setTimeout(() => {
          this.scrollToSelector('.payment-form');
        }, 100);
      },
      paypalSubmit() {
        if (this.$v.form.deal.$invalid) {
          this.$v.form.deal.$touch();

          const element = document.querySelector('.main__deal');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.isOpenPromotionModal = true;
        } else if (this.$v.form.variant.$invalid) {
          this.$v.form.variant.$touch();

          const element = document.querySelector('.variant-selection');

          if (element && element.scrollIntoView) {
            element.scrollIntoView();
          }

          this.isOpenPromotionModal = true;
        }
      },
      paypalCreateOrder () {
        let ipqsResult = null;

        this.form.paymentProvider = 'paypal';

        const currency = !js_query_params.cur || js_query_params.cur === '{aff_currency}'
          ? js_data.product.prices.currency
          : js_query_params.cur;

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentProvider: 'paypal',
        });

        this.paypalPaymentError = '';

        return Promise.resolve()
          .then(() => {
            const data = {
              order_amount: this.getOrderAmount(this.form.deal, this.form.isWarrantyChecked),
            };

            return ipqsCheck(data);
          })
          .then(result => {
            ipqsResult = result;
          })
          .then(() => {
            if (ipqsResult && ipqsResult.recent_abuse) {
              if (this.form.country === 'br') {
                this.log_data('BRAZIL: EMC1 - PayPal - IPQS - recent_abuse', {
                  fraud_chance: ipqsResult.fraud_chance,
                  ipqs: ipqsResult,
                });
              }

              setTimeout(() => {
                this.paypalPaymentError = t('checkout.abuse_error');
              }, 1000);

              return new Promise(resolve => {});
            }

            return paypalCreateOrder({
              xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
              sku_code: this.form.variant,
              sku_quantity: this.form.deal,
              is_warranty_checked: this.form.isWarrantyChecked,
              page_checkout: document.location.href,
              cur: currency,
              offer: js_query_params.offer || null,
              affiliate: js_query_params.affiliate || null,
              ipqsResult,
            });
          })
          .then(res => {
            if (res.error && this.form.country === 'br') {
              this.log_data('BRAZIL: EMC1 - PayPal - response', {
                error: res.paypalPaymentError,
                res: { ...res, paypalPaymentError: undefined },
                fraud_chance: ipqsResult.fraud_chance,
                ipqs: ipqsResult,
              });
            }

            if (res.paypalPaymentError) {
              this.paypalPaymentError = res.paypalPaymentError;
            }

            return res;
          })
          .catch(err => {
            this.paypalPaymentError = t('checkout.payment_error');
          });
      },
      paypalOnApprove(data) {
        return paypalOnApprove(data);
      },
      setPromotionalModal (val) {
        this.isOpenPromotionModal = val
      },
      refreshTopBlock() {
        const oldPrice = document.querySelector('#old-price');
        const newPrice = document.querySelector('#new-price');
        let oldValueText, valueText;

        switch(this.form.installments) {
          case 3:
            oldValueText = js_data.product.prices[1].installments3_old_value_text;
            valueText = js_data.product.prices[1].installments3_value_text;
            break;
          case 6:
            oldValueText = js_data.product.prices[1].installments6_old_value_text;
            valueText = js_data.product.prices[1].installments6_value_text;
            break;
          case 1:
          default:
            oldValueText = js_data.product.prices[1].old_value_text;
            valueText = js_data.product.prices[1].value_text;
            break;
        }

        if (oldPrice) {
          document.querySelector('#old-price').innerHTML = this.quantityOfInstallments + oldValueText;
        }

        if (newPrice) {
          document.querySelector('#new-price').innerHTML = this.quantityOfInstallments + valueText;
        }
      },
      getStepOrder(number) {
        return !this.isShowVariant ? number - 1 : number;
      },
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
          if (!this.disableAnimation) {
            const imgPreload = new Image();
            imgPreload.src = newProductImage;

            fade('out', 300, document.querySelector('#product-image'), true)
              .then(() => {
                this.productImage = newProductImage;
                setTimeout(() => fade('in', 300, document.querySelector('#product-image'), true), 200);
              });
          } else {
            this.productImage = newProductImage;
          }
        }
      },
    },
    mounted() {
      const qty = +this.queryParams.qty;
      const deal = this.purchase.find(({ totalQuantity }) => qty === totalQuantity);

      if (deal) {
        this.form.deal = qty;
      }

      this.refreshTopBlock();
      this.lazyload_update();
    },
    updated() {
      this.lazyload_update();
    },
  }
</script>

<style lang="scss">
    @import "../../sass/variables";
    $white: #fff;
    $color_flush_mahogany_approx: #e74c3c;
    $red: #e74c3c;
    $color_niagara_approx: #16a085;

    .tpl-emc1, .tpl-emc1b {

      .container {
        max-width: 1000px;

        > .row {
          justify-content: space-between;
          margin: 0;

          > .col-md-7 {
            flex: 0 0 55%;
            max-width: 55%;
            padding: 0;
          }

          > .col-md-5 {
            flex: 0 0 42%;
            max-width: 42%;
          }
        }
      }

      @media (min-width: 768px) {
        .container {
          max-width: 750px;
        }
      }

      @media (min-width: 992px) {
        .container {
          max-width: 970px;
        }
      }

      @media (min-width: 1200px) {
        .container {
          max-width: 1000px;
        }
      }

      @media (max-width: 700px) {
        .container > .row {
          > .col-md-7, .col-md-5 {
            flex: 0 0 100%;
            max-width: 100%;
          }

          .col-md-5 {
            margin-top: 20px;
          }
        }
      }

      .offer {
        text-align: center;
      }

      .step1-title {
        margin-top: 20px;
      }

      .step1-titles {
        display: flex;
        margin: 17px 8px 17px 12px;

        h3 {
          margin: 0;
          padding: 0;

          &:first-child {
            flex-grow: 1;
          }
        }
      }

      #warranty-field-button {
        margin-top: 22px;

        label[for=warranty-field] {
          @media screen and (max-width: 991px) {
            & {
              font-size: 14px;
              margin: 18px 40px 18px 80px;

              [dir="rtl"] & {
                margin: 18px 80px 18px 40px;
              }
            }
          }
        }
      }

      .price-total {
        display: flex;
        font-size: 17px;
        margin: 10px 20px 5px;

        .value {
          margin-left: auto;
        }
      }
    }

    .accessories-modal {
        & > p {
            text-align: center;
            font-size: 17px;
        }

        &__bottom {
            display: flex;
            flex-direction: column;

            button {
                margin: 50px auto 10px;
                width: 70%;
                max-width: 395px;
                padding: 5px;
            }

            .thanks {
                font-size: 17px;
                margin: 0 auto;
                border: 0;
                background-color: transparent;
                cursor: pointer;
                text-decoration: underline;
            }
        }
    }

    .main {
        padding-top: 20px;

        & > .row {
            align-items: flex-start;
        }

        .col-7 {
            padding-right: 20px;
        }

        &__deal__text {
            font-size: 18px;
            font-style: italic;
            padding-left: 20px;
            margin: 0;

            strong {
                font-size: 20px;
                font-weight: bold;
            }

            [dir="rtl"] & {
              padding-left: 0;
              padding-right: 20px;
            }
        }

        .radio-button-group {
            .label-container-radio__label {
                font-weight: bold;
                font-size: 16px;
            }

            .label-container-radio__name-price {
                display: flex;
                justify-content: space-between;
            }

            .label-container-radio__best-seller {
                display: flex;
                justify-content: space-between;
                color: $red;
                .label-container-radio__price {
                    margin-left: auto;
                }
            }

            .label-container-radio__best-seller__price {
              color: $red;
            }

            .label-container-radio__discount {
                display: flex;
                justify-content: space-between;

                .discount-text {
                  color: $color_niagara_approx;

                  &.red {
                    color: $red;
                  }
                }
            }

            .label-container-radio.with-discount .label-container-radio__name-price > span:nth-child(2) {
                text-decoration: line-through;
            }

            .label-container-radio__best-seller__soldout,
            .label-container-radio__name-price__soldout {
                display: none;
            }
        }

        .label-container-radio.disabled {
            .label-container-radio__name-price {
                flex-wrap: wrap;
            }
            .label-container-radio__name-price__name {
                order: 2;
                flex: 0 0 100%;
            }
            .label-container-radio__name-price__soldout {
                display: inline-block;
            }
            .label-container-radio__best-seller__soldout {
                display: inline-block;
                margin-right: auto;
                &:before {
                    display: inline-block;
                    padding: 0 3px;
                    content: '-';
                }
            }
        }
        .label-container-radio.disabled.labeled {
            .label-container-radio__name-price {
                flex-wrap: nowrap;
            }
            .label-container-radio__name-price__name {
                order: 0;
                flex: 1 1 auto;
            }
        }

        &__deal {
            h3 {
                padding-left: 20px;
            }

            .share {
                position: absolute;
                transform: rotate3d(-10,-3,0,180deg);
                height: auto;
                width: 40px;
                top: -17px;
                left: -30px;

                [dir="rtl"] & {
                  left: auto;
                  right: -30px;
                  transform: rotate(145deg);
                }

                @media screen and ($s-down) {
                    width: 24px;
                    top: 0;
                    left: -9px;

                    [dir="rtl"] & {
                      left: auto;
                      right: -9px;
                    }
                }
            }

            .radio-button-group {
                .label-container-radio {
                    &:nth-child(2) {
                        background: #fef036;
                    }

                    &:hover {
                        background: #fef9ae;
                    }
                }
            }
        }

        &__payment {
            display: flex;
            flex-direction: column;

            h2, h3 {
                margin: 10px 0;
            }

            h3 {
                margin-top: 0;
            }

            img {
                align-self: center;
                flex-shrink: 0;
                max-width: 100%;
                height: auto;
            }
        }

        &__credit-card-switcher {
            width: 100%;
            padding: 0 !important;
            height: auto !important;
            margin-bottom: 0;

            .label-container-radio {
                background-color: transparent !important;
                border: 0 !important;
                color: $white;
                cursor: pointer;
                margin: 0 0 10px;

                &.bank-payment {
                    border-radius: 3px;
                    background-image: linear-gradient(to bottom,#fff 0,#f6f6f6 53%,#ececec 100%);
                    border: 1px solid #efefef;
                    color: #333;

                    &:before {
                        opacity: 0;
                        font-family: FontAwesome!important;
                        content: '\f054';
                        width: 0;
                        height: 100%;
                        position: absolute;
                        top: 0;
                        left: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        border-radius: 0 50% 50% 0;
                        background-color: rgba(255,255,255,.3);
                        transition: all .2s linear 0s;
                    }

                    &:hover {
                        background-color: #e6e6e6;
                        background-image: linear-gradient(to bottom,#e6e6e6 0,#ddd 53%,#d3d3d3 100%);

                        &:before {
                            opacity: 1;
                            width: 30px;
                        }
                    }

                    .checkmark {
                        border-color: #333;

                        &:after {
                            background-color: #333;
                        }
                    }
                }

                &:first-child {
                    height: auto;
                    border: 1px solid #0f9b0f;
                    background: #ff2f21 linear-gradient(#0f9b0f, #0d840d) repeat scroll 0% 0%/auto padding-box border-box;

                    &:hover {
                        background-image: linear-gradient(to bottom, #6d4 0, #3d6c04 100%);
                    }
                }

                &:hover {
                    background-color: #e6e6e6;
                }

                &__label {
                    font-size: 15px;
                }

                .checkmark {
                    border-color: $white;
                    background-color: transparent;

                    &:after {
                        background-color: $white;
                    }
                }
            }
        }

        &__bottom {
            width: 100%;
            display: flex;
            flex-direction: column;

            img {
                width: 80%;
                margin: 0 auto;
            }

            p {
                padding: 4px 24px;
                text-align: center;
                font-size: 13px;

                i {
                    position: relative;
                    margin-right: 4px;
                    top: 2px;
                    font-size: 18px;
                    color: #409EFF;

                    [dir="rtl"] & {
                      margin-left: 4px;
                      margin-right: 0;
                    }
                }
            }
        }

        @media screen and (max-width: 767px) {
            .col-md-7 {
                padding: 0;
            }
        }

        .variant-selection {
          margin-top: 10px;
        }

        .variant-field-label {
          display: none;
        }
    }

    *[dir=rtl] {
        .radio-button-group .label-container-radio {
            padding: 15px 45px 15px 15px;

            .checkmark {
                left: unset;
                right: 15px;
            }
        }

        .main__payment {
            .first-name {
                margin-right: 0;
            }

            .last-name {
                margin-right: 0;
            }

            .card-date {
                padding-right: 0;
                padding-left: 30px;

                & > div {
                    margin-right: 0;

                    &:last-child {
                        margin-right: 10px;
                    }
                }
            }
        }

        .iti__flag-container {
            left: auto;
        }
    }

    .special-offer-popup {
        .el-dialog {
            margin-top: 10vh !important;
        }
    }

    @media screen and ($s-down) {
        .special-offer-popup {
            .el-dialog {
                width: 100%;
                margin-top: 0 !important;

                .accessories-modal {
                    &__list {
                        .product-item {
                            &__image {
                                padding: 0;
                            }

                            &__main {
                                padding-left: 20px;
                            }
                        }
                    }
                }
            }
        }

        .accessories-modal__bottom {
            button {
                width: 100%;
            }
        }
    }
</style>
