<template>
    <div v-if="$v && !hidePage">
        <div class="container">
            <ProductOffer :product="checkoutData.product" />
        </div>
        <div class="container main">
            <div class="row">
                <div class="col-md-7">
                    <div class="paper main__deal">
                        <div class="d-flex">
                            <div class="main__sale">
                                <div class="sale-badge dynamic-sale-badge ">
                                    <div class="dynamic-sale-badge__background"></div>
                                    <div class="dynamic-sale-badge__container" v-html="textDynamicSaleBadge"></div>
                                </div>
                            </div>
                            <p class="main__deal__text" v-html="textMainDealText"></p>
                        </div>
                        <h2><span v-html="textStep"></span> 1: <span v-html="textChooseDeal"></span></h2>
                        <select-field
                                v-if="form.country === 'mx' && form.cardType === 'credit'"
                                :label="textInstallmentsTitle"
                                popperClass="emc1-popover-variant"
                                :list="installmentsList"
                                v-model="form.installments"
                                @input="setImplValue"
                        />

                        <h3 v-html="textArtcile"></h3>

                        <span class="error" v-show="$v.form.deal.$dirty && $v.form.deal.$invalid" v-html="textMainDealError"></span>

                        <radio-button-group
                                v-model="form.deal"
                                :list="dealList"
                                @input="setWarrantyPriceText"
                                :validation="$v.form.deal"
                        />

                        <div v-show="variantList.length > 1 && !isShowVariant">
                            <h2><span v-html="textStep"></span> 2: <span v-html="textSelectVariant"></span></h2>
                            <!-- TODO: check if this is useless, remove it:
                            warrantyPriceText="setWarrantyPriceText()"  -->
                            <select-field
                                    popperClass="emc1-popover-variant"
                                    v-model="form.variant"
                                    :validation="$v.form.variant"
                                    validationMessage="Invalid field"
                                    :config="{
                  prefix: 'EchoBeat7'
                }"
                                    :rest="{
                  placeholder: 'Variant'
                }"
                                    :list="variantList"
                                    warrantyPriceText="setWarrantyPriceText()"
                            />
                        </div>

                        <transition name="el-zoom-in-top">
                            <button v-show="warrantyPriceText" id="warranty-field-button">
                                <label for="warranty-field" class="label-container-checkbox">
                                    <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
                                    <input id="warranty-field" type="checkbox" v-model="form.isWarrantyChecked">
                                    <span class="checkmark"></span>
                                </label>
                                <img :src="$root.cdnUrl + '/assets/images/best-saller.png'" alt="">
                                <i class="fa fa-arrow-right slide-right"></i>
                            </button>
                        </transition>
                    </div>
                </div>
                <div class="paper col-md-5 main__payment">
                    <img id="product-image" :src="setProductImage" alt="">
                    <template v-if="!isPurchasAlreadyExists">
                        <h2><span v-html="textStep"></span> {{ getStepOrder(3) }}: <span v-html="textPaymentMethod"></span></h2>
                        <h3 v-html="textPaySecurely"></h3>
                        <payment-type-radio-list
                                v-model="form.paymentType"
                                :country="form.country"
                                @input="activateForm" />
                        <paypal-button
                                :createOrder="paypalCreateOrder"
                                :onApprove="paypalOnApprove"
                                v-show="fullAmount"
                                :$v="$v.form.deal"
                                @click="paypalSubmit"
                        >{{ paypalRiskFree }}</paypal-button>
                        <transition name="el-zoom-in-top">
                            <payment-form
                                    :firstTitle="`${textStep} ${getStepOrder(4)}: ${textContactInformation}`"
                                    :secondTitle="`${textStep} ${getStepOrder(5)}: ${textDeliveryAddress}`"
                                    :thirdTitle="`${textStep} ${getStepOrder(6)}: ${textPaymentDetails}`"
                                    v-if="form.paymentType && isFormShown"
                                    @showCart="isOpenSpecialOfferModal = true"
                                    :$v="$v"
                                    :installments="form.installments"
                                    :paymentForm="form"
                                    :countryCode="checkoutData.countryCode"
                                    :isBrazil="checkoutData.countryCode === 'br'"
                                    :countryList="setCountryList"
                                    @setPromotionalModal="setPromotionalModal"
                                    @setAddress="setAddress"/>
                        </transition>
                        <div class="main__bottom">
                            <img :src="$root.cdnUrl + '/assets/images/safe_payment_en.png'" alt="safe payment">
                            <p><i class="fa fa-lock"></i><span v-html="textSafeSSLEncryption"></span></p>
                            <p><span v-html="textCreditCardInvoiced"></span> "{{ productData.billing_descriptor }}"</p>
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
                :title="textMainDealErrorPopupTitle"
                :lock-scroll="false"
                :visible.sync="isOpenPromotionModal">
            <div class="cvv-popup__content">
                <p class="error-container" v-html="textMainDealErrorPopupMessage"></p>

                <button
                        @click="isOpenPromotionModal = false"
                        style="height: 67px; margin: 0"
                        type="button"
                        class="green-button-animated">
                    <span class="purchase-button-text" v-html="textMainDealErrorPopupButton"></span>
                </button>
            </div>
        </el-dialog>

        <el-dialog
                @click="isOpenSpecialOfferModal = false"
                class="common-popup special-offer-popup"
                :title="textSpecialOfferPopupTitle"
                :visible.sync="isOpenSpecialOfferModal">
            <div class="common-popup__content accessories-modal">
                <p v-html="textSpecialOfferPopupMessage"></p>
                <Cart
                        @setCart="setCart"
                        :productList="mockData.productList"
                        :cart="cart"/>
                <div class="accessories-modal__list">
                    <ProductItem
                            v-for="item in mockData.productList"
                            @setCart="setCart"
                            :key="item.key"
                            :keyProp="item.key"
                            :value="cart[item.key]"
                            :imageUrl="item.imageUrl"
                            :title="item.title"
                            :advantageList="item.advantageList"
                            :regularPrice="item.regularPrice"
                            :newPrice="item.newPrice"
                            :maxQuantity="3"
                    />
                </div>
                <div class="accessories-modal__bottom">
                    <button
                            style="height: auto;"
                            type="button"
                            class="green-button-animated">
                        <span class="purchase-button-text" v-html="textSpecialOfferPopupButtonPurchase"></span>
                    </button>
                    <button
                            v-if="isEmptyCart"
                            v-html="textSpecialOfferPopupButtonEmpty"
                            @click="isOpenSpecialOfferModal = false"
                            class="thanks"></button>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>
  import emc1Validation from '../validation/emc1-validation'
  import notification from '../mixins/notification'
  import queryToComponent from '../mixins/queryToComponent'
  import { t } from '../utils/i18n';
  import { getNotice, getRadioHtml } from '../utils/emc1';
  import { getCountOfInstallments } from '../utils/installments';
  import ProductItem from './common/ProductItem';
  import Cart from './common/Cart';
  import ProductOffer from '../components/common/ProductOffer';
  import PurchasAlreadyExists from './common/PurchasAlreadyExists';
  import { fade } from '../utils/common';
  import { preparePurchaseData, goToThankYouPromos } from '../utils/checkout';
  import purchasMixin from '../mixins/purchas';
  import { preparePartByInstallments } from '../utils/installments';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
  import { queryParams } from  '../utils/queryParams';

  export default {
    name: 'emc1',
    mixins: [
      notification,
      queryToComponent,
      purchasMixin,
    ],
    components: {
      ProductItem,
      Cart,
      ProductOffer,
      PurchasAlreadyExists,
    },
    props: ['showPreloader', 'skusList'],
    data () {
      return {
        hidePage: false,
        isFormShown: false,
        selectedProductData: {
          prices: null,
          quantity: null,
        },
        ImplValue: null,
        radioIdx: null,
        warrantyPriceText: null,
        mockData: {
          productList: [
            {
              key: 0,
              name: 'Echo Beat - Wireless 3D Sound white',
              title: '+1 Echo Beat - Wireless 3D Sound - 50% discount per unit',
              imageUrl: window.cdnUrl + '/assets/images/headphones-white.png',
              advantageList: [
                'High Sound',
                'Portable Charging',
                'Ergonomic Design',
                'iOs & Android',
              ],
              regularPrice: 69.98,
              newPrice: 34.99,
            }, {
              key: 1,
              name: 'Echo Beat - Wireless 3D Sound gold',
              title: '+1 Echo Beat - Wireless 3D Sound - 50% discount per unit',
              imageUrl: window.cdnUrl + '/assets/images/headphones-gold.png',
              advantageList: [
                'High Sound',
                'Portable Charging',
                'Ergonomic Design',
                'iOs & Android',
              ],
              regularPrice: 69.98,
              newPrice: 34.99,
            }, {
              key: 2,
              name: 'Echo Beat - Wireless 3D Sound red',
              title: '+1 Echo Beat - Wireless 3D Sound - 50% discount per unit',
              imageUrl: window.cdnUrl + '/assets/images/headphones-red.png',
              advantageList: [
                'High Sound',
                'Portable Charging',
                'Ergonomic Design',
                'iOs & Android',
              ],
              regularPrice: 69.98,
              newPrice: 34.99,
            }
          ],
        },
        cart: {},
        purchase: [],
        variantList: [],
        installmentsList: [
          {
            label: t('checkout.installments.full_amount'),
            text: t('checkout.installments.full_amount'),
            value: 1,
          }, {
            label: t('checkout.installments.pay_3'),
            text: t('checkout.installments.pay_3'),
            value: 3,
          }, {
            label: t('checkout.installments.pay_6'),
            text: t('checkout.installments.pay_6'),
            value: 6,
          }
        ],
        form: {
          isWarrantyChecked: false,
          countryCodePhoneField: checkoutData.countryCode,
          deal: null,
          variant: (function() {
            try {
              return checkoutData.product.skus[0].code
            } catch(_) {}
          }()),
          installments: 1,
          paymentType: null,
          fname: null,
          lname: null,
          dateOfBirth: '',
          email: null,
          phone: null,
          cardType: 'credit',
          street: null,
          number: null,
          complemento: null,
          city: null,
          state: null,
          zipcode: null,
          country: checkoutData.countryCode,
          cardNumber: '',
          month: null,
          year: null,
          cvv: null,
          documentNumber: ''
        },
        isOpenPromotionModal: false,
        isOpenSpecialOfferModal: false,
      }
    },
    created() {
      if (this.queryParams['3ds'] === 'success') {
        this.hidePage = true;
        return goToThankYouPromos();
      }

      if (this.queryParams['3ds'] === 'failure') {
        const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData'));

        if (selectedProductData) {
          this.form.deal = selectedProductData.deal || this.form.deal;
          this.form.variant = selectedProductData.variant || this.form.variant;
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.installments = selectedProductData.installments || this.form.installments;
          this.form.paymentType = selectedProductData.paymentType || this.form.paymentType;
          this.form.cardType = selectedProductData.cardType || this.form.cardType;
          this.form.fname = selectedProductData.fname || this.form.fname;
          this.form.lname = selectedProductData.lname || this.form.lname;
          this.form.dateOfBirth = selectedProductData.dateOfBirth || this.form.dateOfBirth;
          this.form.email = selectedProductData.email || this.form.email;
          this.form.phone = selectedProductData.phone || this.form.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.street = selectedProductData.street || this.form.street;
          this.form.number = selectedProductData.streetNumber || this.form.number;
          this.form.complemento = selectedProductData.complemento || this.form.complemento;
          this.form.city = selectedProductData.city || this.form.city;
          this.form.state = selectedProductData.state || this.form.state;
          this.form.zipcode = selectedProductData.zipcode || this.form.zipcode;
          this.form.country = selectedProductData.country || this.form.country;
          this.setWarrantyPriceText(this.form.deal);
          this.isFormShown = true;
        }
      }
    },
    computed: {
      setProductImage() {
        return this.productData.image[this.queryParams['image'] - 1] || this.productData.image[0];
      },
      isShowVariant() {
        return Number(queryParams().variant) === 0
      },
      setCountryList () {
        const countries = checkoutData.countries;
        let countriesList = [];

        Object.keys(countries).map(function(key) {
          countriesList.push({
            value: key,
            text: countries[key],
            label: countries[key]
          });
        });

        return countriesList;
      },
      codeOrDefault () {
        return this.queryParams.product || this.checkoutData.product.skus[0].code;
      },
      fullAmount () {
        return this.form.installments == 1;
      },
      productData () {
        return checkoutData.product
      },
      isEmptyCart () {
        return Object.values(this.cart).every(it => it === 0)
      },
      withInstallments () {
        return this.form.country === 'br'
          || this.form.country === 'mx'
          || this.form.country === 'co'
      },
      quantityOfInstallments () {
        const { installments } = this.form
        return installments && installments !== 1 ? installments + '× ' : ''
      },
      dealList () {
        const isSellOutArray = queryParams().sellout
          ? queryParams().sellout.split(',')
          : [];

        return this.purchase.map((it, idx) => ({
          value: it.totalQuantity,
          quantity: it.totalQuantity,
          isOutOfStock: isSellOutArray.includes(String(it.totalQuantity)),
          isLabeled: !!it.discountName,
          label: getRadioHtml({
            ...it,
            installments: this.form.installments,
            text: it.text,
            idx,
          })
        }))
      },
      checkoutData () {
        return checkoutData
      },
      warrantyPrice () {
        const currentDeal = this.purchase.find(it => +it.totalQuantity === +this.form.deal)

        return currentDeal && Math.round((currentDeal.newPrice || currentDeal.price) * 10) / 100
      },
      textDynamicSaleBadge: () => t('checkout.dynamic_sale_badge'),
      textMainDealText: () => t('checkout.main_deal.message'),
      textStep: () => t('checkout.step'),
      textChooseDeal: () => t('checkout.choose_deal'),
      textInstallmentsTitle: () => t('checkout.installments.title'),
      textArtcile: () => t('checkout.article'),
      textMainDealError: () => t('checkout.main_deal.error'),
      textMainDealErrorPopupTitle: () => t('checkout.main_deal.error_popup.title'),
      textMainDealErrorPopupMessage: () => t('checkout.main_deal.error_popup.message'),
      textMainDealErrorPopupButton: () => t('checkout.main_deal.error_popup.button'),
      textSelectVariant: () => t('checkout.select_variant'),
      textWarranty: () => t('checkout.warranty'),
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
    },
    watch: {
      'form.installments' (val) {
        this.setPurchase({
          variant: this.form.variant,
          installments: val,
        })
      },
      'form.variant' (val) {
        fade('out', 300, document.querySelector('#product-image'), true)
          .then(() => {
            this.productImage = this.variantList.find(variant => variant.value === val).imageUrl

            setTimeout(() => fade('in', 300, document.querySelector('#product-image'), true), 200)
          })

        this.setPurchase({
          variant: val,
          installments: this.form.installments,
        })
      },
    },
    validations: emc1Validation,
    methods: {
      activateForm() {
        this.isFormShown = true;
        this.$nextTick(() => {
          document.querySelector('.payment-form').scrollIntoView();
        })
      },
      paypalSubmit() {
        this.form.paymentType = 'paypal';

        if (this.$v.form.deal.$invalid) {
          document.querySelector('.main__deal').scrollIntoView();
          this.isOpenPromotionModal = true;
        }
      },

      setImplValue(value) {
        this.implValue = value;
        if (this.radioIdx) this.changeWarrantyValue();
      },
      setWarrantyPriceText(radioIdx) {
        this.radioIdx = Number(radioIdx);
        this.changeWarrantyValue();
      },
      changeWarrantyValue () {
        const prices = this.checkoutData.product.prices;

        if (!this.implValue) {
          this.implValue = this.withInstallments
            ? 3
            : 1;
        }

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
            break;
        }
      },
      paypalCreateOrder () {
        const searchParams = new URL(document.location.href).searchParams;
        const currency = searchParams.get('cur') || checkoutData.product.prices.currency;

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentType: this.form.paymentType,
        });

        return paypalCreateOrder({
          xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
          sku_code: this.codeOrDefault,
          sku_quantity: this.form.deal,
          is_warranty_checked: this.form.isWarrantyChecked,
          page_checkout: document.location.href,
          cur: currency,
          offer: searchParams.get('offer'),
          affiliate: searchParams.get('affiliate'),
        })
      },
      paypalOnApprove: paypalOnApprove,
      setCart (cart) {
        this.cart = {
          ...this.cart,
          ...cart,
        }
      },
      setPromotionalModal (val) {
        this.isOpenPromotionModal = val
      },
      setAddress (address) {
        this.form = {
          ...this.form,
          ...address
        }
      },
      setPurchase ({ variant, installments }) {
        const oldPrice = document.querySelector('#old-price');
        const newPrice = document.querySelector('#new-price');
        let oldValueText, valueText;

        switch(installments) {
          case 3:
            oldValueText = checkoutData.product.prices[1].installments3_old_value_text;
            valueText = checkoutData.product.prices[1].installments3_value_text;
            break;
          case 6:
            oldValueText = checkoutData.product.prices[1].installments6_old_value_text;
            valueText = checkoutData.product.prices[1].installments6_value_text;
            break;
          case 1:
          default:
            oldValueText = checkoutData.product.prices[1].old_value_text;
            valueText = checkoutData.product.prices[1].value_text;
            break;
        }

        if (oldPrice) {
          document.querySelector('#old-price').innerHTML = getCountOfInstallments(installments) + oldValueText;
        }

        if (newPrice) {
          document.querySelector('#new-price').innerHTML = getCountOfInstallments(installments) + valueText;
        }

        const currentVariant = this.skusList.find(it => it.code === variant)

        this.purchase = preparePurchaseData({
          purchaseList: this.productData.prices,
          long_name: this.productData.long_name,
          variant: currentVariant && currentVariant.name,
          installments,
        })
      },
      getStepOrder(number) {
        return this.variantList.length == 1 || this.isShowVariant ? number - 1 : number
      }
    },
    mounted () {
      window.setTestData = () => {
        this.form = {
          ...this.form,
          fname: 'Name',
          lname: 'LName',
          email: 'email@gmail.mail',
          phone: '44444444',
          zipcode: '13010-111',
          number: '111',
          complemento: 'Complemento',
          cardNumber: '378282246310005',
          cvv: '123',
          year: 2021,
          month: 1,
          documentNumber: '111.111.111-11',
        }
      }

      try {
        this.productImage = this.productData.image[0];
      } catch (_) {}

      this.setCart(this.mockData.productList.reduce((acc, { key }) => {
        acc[key] = 0

        return acc
      }, {}))

      this.variantList = this.skusList.map((it) => ({
        label: it.name,
        text: `<div><img src="${it.quantity_image[1]}" alt=""><span>${it.name}</span></div>`,
        value: it.code,
        imageUrl: it.quantity_image[1]
      }))

      this.setPurchase({
        variant: this.form.variant,
        installments: this.form.installments,
      })

      if (this.withInstallments) {
        this.form.installments =
          this.checkoutData.countryCode === 'br' ? 3 :
            this.checkoutData.countryCode === 'mx' ? 1 :
              1
      }

      const qty = +this.queryParams.qty;
      const deal = this.purchase.find(({ totalQuantity }) => qty === totalQuantity);

      if (deal) {
        this.setWarrantyPriceText(qty);
        this.form.deal = qty;
      }
    }
  }
</script>

<style lang="scss">
    @import "../../sass/variables";
    $white: #fff;
    $color_flush_mahogany_approx: #e74c3c;
    $red: #e74c3c;
    $color_niagara_approx: #16a085;

    .tpl-emc1 {
      .offer {
        text-align: center;
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

    .sale-badge {
        width: 85px;
        height: 85px;
        margin-left: 5px;
        margin-top: 5px;
        justify-content: center;
        align-items: center;
        color: $white;
        font-weight: 700;
        font-size: 2.5rem;
        position: relative;
    }

    .dynamic-sale-badge__background {
        animation: spin 20s linear infinite;
        position: absolute;
        background: $color_flush_mahogany_approx;
        border-radius: 50%;
        padding: 5px;
        border: 2px dashed $white;
        box-shadow: 0 0 0 5px $color_flush_mahogany_approx;
        width: 85px;
        height: 85px;
    }

    .dynamic-sale-badge__container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        font-size: 12px;
        text-align: center;
        transform: rotate(349deg);
        position: absolute;
        width: 85px;
        height: 85px;
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

            .label-container-radio__discount {
                color: $color_niagara_approx;
                &.red {
                    color: $red;
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

                @media screen and ($s-down) {
                    width: 24px;
                    top: 0;
                    left: -9px;
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

        &__payment {
            display: flex;
            flex-wrap: wrap;

            h2, h3 {
                margin: 10px 0;
            }

            h3 {
                margin-top: 0;
            }

            img {
                width: 100%;
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
                text-align: center;
                font-size: 13px;

                i {
                    position: relative;
                    margin-right: 4px;
                    top: 2px;
                    font-size: 18px;
                    color: #409EFF;
                }
            }
        }

        @media screen and (max-width: 768px) {
            .col-md-7 {
                padding: 0;
            }

            &__deal {
                #warranty-field-button {
                    label[for=warranty-field] {
                        padding: 0 0 0 70px;
                        display: flex;
                        align-items: center;
                        font-size: 0.8rem;
                    }
                }
            }
        }
    }

    .emc1-popover-variant {
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
                    margin-left: 10px;
                    margin-right: 10px;
                }
            }
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
                margin-right: 10px;
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
