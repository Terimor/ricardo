<template>

  <div
    v-if="$v && !showPreloader"
    class="fmc5">

    <div class="gray-back"></div>

    <div class="inside">

      <div class="steps-line">
        <div class="step-line">
          <div class="step-line-triangle"></div>
          <div>01. ORDER</div>
        </div>
        <div class="step-line">
          <div class="step-line-triangle"></div>
          <div>02. OFFER</div>
        </div>
        <div class="step-line">
          <div class="step-line-triangle"></div>
          <div>03. CONFIRM</div>
        </div>
      </div>

      <div class="content">
        
        <div class="content-left">

          <div class="step">
            <img :src="imageStep" />
            <strong>Step #{{ step }}:&nbsp;</strong>
            <span v-if="step === 1">Select Quantity</span>
            <span v-if="step === 2">Shipping Information</span>
            <span v-if="step === 3">Payment Information</span>
          </div>

          <transition name="fade">
            <div
              v-if="step === 1"
              class="step1">

              <Installments
                :extraFields="extraFields"
                :form="form" />

              <div
                class="deals scroll-when-error"
                :class="{ invalid: $v.form.deal.$invalid }">

                <div
                  v-for="deal in deals"
                  class="deal"
                  :class="{
                    selected: deal.quantity === form.deal,
                    sellout: deal.sellout,
                  }"
                  @click="setDeal(deal)">

                  <div class="deal-separator"></div>

                  <div
                    v-if="deal.sellout"
                    class="deal-sellout"
                    @click.stop></div>

                  <div
                    v-if="deal.is_bestseller || deal.is_popular"
                    class="deal-special">

                    <div class="deal-left">

                      <div
                        v-if="deal.is_bestseller"
                        class="deal-bestseller">
                        <div class="deal-special-triangle"></div>
                        <div>Bestseller</div>
                      </div>

                      <div
                        v-if="deal.is_popular"
                        class="deal-popular">
                        <div class="deal-special-triangle"></div>
                        <div>Best Deal</div>
                      </div>

                    </div>

                    <div class="deal-right">
                      <div class="deal-sameas">Same As</div>
                    </div>

                  </div>

                  <div class="deal-content">

                    <div class="deal-left">

                      <div class="deal-radio radio-button">
                        <div v-if="deal.quantity === form.deal"></div>
                      </div>

                      <div class="deal-label">
                        <div class="deal-count">{{ dealsMainQuantities[deal.quantity] }}x</div>
                        <div class="deal-name">&nbsp;{{ product.product_name }}&nbsp;</div>
                        <div
                          v-if="dealsFreeQuantities[deal.quantity]"
                          class="deal-free">+ {{ dealsFreeQuantities[deal.quantity] }} Free</div>
                      </div>

                      <div class="deal-discount">
                        <div class="deal-discount-o">o</div>
                        <div class="deal-discount-value">{{ deal.discount_percent }}%</div>
                        <div class="deal-discount-off">&nbsp;OFF</div>
                      </div>

                    </div>

                    <div class="deal-right">

                      <div class="deal-price-one">
                        <div v-if="form.installments === 6">6x {{ deal.installments6_unit_value_text }}</div>
                        <div v-if="form.installments === 3">3x {{ deal.installments3_unit_value_text }}</div>
                        <div v-if="form.installments === 1">{{ deal.unit_value_text }}</div>
                        <div v-if="deal.quantity > 1">&nbsp;/ea</div>
                      </div>

                      <div class="deal-price-total">
                        <div v-if="form.installments === 6">(6x {{ deal.installments6_value_text }})</div>
                        <div v-if="form.installments === 3">(3x {{ deal.installments3_value_text }})</div>
                        <div v-if="form.installments === 1">({{ deal.value_text }})</div>
                      </div>

                      <div class="deal-free-shipping">Free Shipping</div>

                    </div>

                  </div>

                </div>

              </div>

            </div>
          </transition>

          <transition name="fade">
            <div
              v-if="step === 2"
              class="step2">

              <div class="form">

                <FirstName
                  :$v="$v.form.fname"
                  :placeholder="true"
                  :form="form"
                  name="fname" />

                <LastName
                  :$v="$v.form.lname"
                  :placeholder="true"
                  :form="form"
                  name="lname" />

                <Email
                  :$v="$v.form.email"
                  :placeholder="true"
                  :form="form"
                  name="email" />

                <Phone
                  :$v="$v.form.phone"
                  :placeholder="true"
                  :ccform="form"
                  ccname="countryCodePhoneField"
                  :form="form"
                  name="phone" />

                <ZipCode
                  v-if="form.country === 'br'"
                  :$v="$v.form.zipcode"
                  :isLoading="isLoading"
                  @setBrazilAddress="setBrazilAddress"
                  :country="form.country"
                  :placeholder="true"
                  :form="form"
                  name="zipcode" />

                <Street
                  :$v="$v.form.street"
                  :isLoading="isLoading"
                  :placeholder="true"
                  :form="form"
                  name="street" />

                <District
                  :extraFields="extraFields"
                  :placeholder="true"
                  :form="form"
                  :$v="$v" />

                <City
                  :$v="$v.form.city"
                  :isLoading="isLoading"
                  :placeholder="true"
                  :form="form"
                  name="city" />

                <State
                  v-if="!extraFields.state"
                  :$v="$v.form.state"
                  :isLoading="isLoading"
                  :country="form.country"
                  :placeholder="true"
                  :form="form"
                  name="state" />

                <EState
                  v-else
                  :country="form.country"
                  :extraFields="extraFields"
                  :isLoading="isLoading"
                  :form="form"
                  :$v="$v" />

                <ZipCode
                  v-if="form.country !== 'br'"
                  :$v="$v.form.zipcode"
                  :isLoading="isLoading"
                  :country="form.country"
                  :placeholder="true"
                  :form="form"
                  name="zipcode" />

                <Country
                  :$v="$v.form.country"
                  :placeholder="true"
                  :form="form"
                  name="country" />

              </div>

            </div>
          </transition>

          <transition name="fade">
            <div
              v-if="step === 3"
              class="step3">

              <payment-provider-radio-list
                v-model="form.paymentProvider" />

              <paypal-button
                v-show="form.installments === 1"
                :createOrder="paypalCreateOrder"
                :onApprove="paypalOnApprove"
                :$vdeal="$v.form.deal"
                @click="paypalSubmit"
              >{{ textPaypalRiskFree }}</paypal-button>

              <div
                v-if="paypalPaymentError"
                id="paypal-payment-error"
                class="error-container invalid"
                v-html="paypalPaymentError"></div>

              <Warranty
                :form="form" />

              <transition name="fade">
                <div
                  v-if="form.paymentProvider === 'credit-card'"
                  class="form">

                  <CardHolder
                    v-if="$root.isAffIDEmpty"
                    :$v="$v.form.cardHolder"
                    :placeholder="true"
                    :form="form"
                    name="cardHolder" />

                  <CardType
                    :extraFields="extraFields"
                    :form="form"
                    :$v="$v" />

                  <CardNumber
                    :$v="$v.form.cardNumber"
                    :paymentMethodURL="paymentMethodURL"
                    @setPaymentMethodByCardNumber="setPaymentMethodByCardNumber"
                    :placeholder="true"
                    :form="form"
                    name="cardNumber" />

                  <CardDate
                    :$v="$v.form.cardDate"
                    :form="form"
                    name="cardDate" />

                  <CVV
                    :$v="$v.form.cvv"
                    :placeholder="true"
                    :form="form"
                    name="cvv" />

                  <DocumentType
                    :extraFields="extraFields"
                    :form="form"
                    :$v="$v" />

                  <DocumentNumber
                    :extraFields="extraFields"
                    :form="form"
                    :$v="$v" />

                  <Terms
                    v-if="$root.isAffIDEmpty && form.paymentProvider === 'credit-card'"
                    :$v="$v.form.terms"
                    :form="form"
                    name="terms" />

                  <div
                    v-if="paymentError"
                    id="payment-error"
                    class="error-container invalid"
                    v-html="paymentError"></div>

                </div>
              </transition>

            </div>
          </transition>

          <div class="buttons">

            <img
              v-if="step > 1"
              :src="imageButtonBack"
              class="button-back"
              @click="backClick" />

            <div
              v-if="step === 1 || step === 2"
              class="button-next"
              @click="nextClick">
              <div>NEXT</div>
            </div>

            <div
              v-if="step === 3 && form.paymentProvider === 'credit-card'"
              :class="{ submitted: isSubmitted }"
              class="button-next"
              @click="nextClick">

              <template v-if="isSubmitted">
                <Spinner />
                <div class="disabled"></div>
              </template>

              <div
                :class="{ hidden: isSubmitted }">
                <div>YES! COMPLETE MY ORDER</div>
              </div>

            </div>

          </div>

        </div>

        <div class="content-right">
          
          <div class="details">

            <div class="details-left">
              <div class="product-name">{{ product.product_name }}</div>
              <div class="product-long-name">{{ product.long_name }}</div>
              <div class="product-bestseller">
                <div class="product-bestseller-triangle"></div>
                <div>#1 Best Seller</div>
              </div>
              <img
                :src="image5star"
                class="product-5star" />
            </div>

            <div class="details-right">
              <img
                :src="product.image[0]"
                class="product-image" />
            </div>

          </div>

          <div
            class="description"
            v-html="productDescription"></div>

          <div class="guarantee">
            <img
              :src="imageGuarantee"
              class="guarantee-image" />
            <div class="guarantee-title">30 Day Money Back Guarantee</div>
            <div class="guarantee-text">You Risk Nothing! 100% Iron-Clad Guarantee!</div>
            <div class="guarantee-text">{{ product.home_name }}</div>
            <div class="guarantee-text">{{ product.splash_description }}</div>
          </div>

        </div>

      </div>

      <div class="reviews">
        
        <div class="reviews-title">
          <div class="reviews-title-left"></div>
          <div class="reviews-title-right"></div>
          <div>CUSTOMER REVIEWS</div>
        </div>

        <div class="reviews-block">

          <div class="rate-box">
            <div class="rate-box-value">4.8</div>
            <div class="rate-box-outof">Out of 5.0</div>
          </div> 

          <img
            :src="imageStarsLines"
            class="stars-lines" />

          <div class="reviews-overall">
            <div>Overall Rating</div>
            <img :src="image5star" />
          </div>

          <div class="reviews-percent">
            <div class="reviews-percent-value">91%</div>
            <div class="reviews-percent-text">of customers that buy from this merchant give them a 5-Star rating</div>
          </div>

        </div>

        <div class="reviews-items">
          <div
            v-for="review of product.reviews"
            class="review">

            <div
              class="review-5star"
              :style="{ width: review.rate * 20 + 'px' }">
              <img :src="image5star"/>
            </div>

            <div class="review-text">{{ review.text }}</div>
            <div class="review-name">{{ review.name }} – {{ review.date }}</div>

            <div class="review-verified">
              <img :src="imageVerifiedCheck" />
              <div>Verified Buyer</div>
            </div>

          </div>
        </div>

      </div>

    </div>

  </div>

</template>


<script>

  import validations from '../validation/fmc5-validation';
  import scrollToError from '../mixins/formScrollToError';
  import purchasMixin from '../mixins/purchas';
  import notification from '../mixins/notification'
  import * as extraFields from '../mixins/extraFields';
  import blackFriday from '../mixins/blackFriday';
  import christmas from '../mixins/christmas';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
  import { sendCheckoutRequest, get3dsErrors } from '../utils/checkout';
  import { ipqsCheck } from '../services/ipqs';
  import FirstName from './common/common-fields/FirstName';
  import LastName from './common/common-fields/LastName';
  import Email from './common/common-fields/Email';
  import Phone from './common/common-fields/Phone';
  import Street from './common/common-fields/Street';
  import City from './common/common-fields/City';
  import State from './common/common-fields/State';
  import ZipCode from './common/common-fields/ZipCode';
  import Country from './common/common-fields/Country';
  import CardHolder from './common/common-fields/CardHolder';
  import CardNumber from './common/common-fields/CardNumber';
  import CardDate from './common/common-fields/CardDate';
  import CVV from './common/common-fields/CVV';
  import Terms from './common/common-fields/Terms';
  import EState from './common/extra-fields/State';
  import District from './common/extra-fields/District';
  import CardType from './common/extra-fields/CardType';
  import DocumentType from './common/extra-fields/DocumentType';
  import DocumentNumber from './common/extra-fields/DocumentNumber';
  import Installments from './common/extra-fields/Installments';
  import Spinner from './common/preloaders/Spinner';
  import Warranty from './common/Warranty';


  export default {

    props: [
      'showPreloader',
    ],


    validations,


    mixins: [
      notification,
      scrollToError,
      extraFields.tplMixin,
      purchasMixin,
      blackFriday,
      christmas,
    ],


    components: {
      FirstName,
      LastName,
      Email,
      Phone,
      Street,
      City,
      State,
      ZipCode,
      Country,
      CardHolder,
      CardNumber,
      CardDate,
      CVV,
      Terms,
      EState,
      District,
      CardType,
      DocumentType,
      DocumentNumber,
      Installments,
      Spinner,
      Warranty,
    },


    data() {
      return {
        step: 1,
        isLoading: {
          address: false,
        },
        form: {
          deal: 3,
          variant: null,
          isWarrantyChecked: false,
          paymentProvider: null,
          fname: null,
          lname: null,
          email: null,
          phone: null,
          countryCodePhoneField: checkoutData.countryCode,
          street: null,
          city: null,
          state: null,
          zipcode: null,
          country: checkoutData.countryCode,
          cardHolder: null,
          cardNumber: null,
          cardDate: null,
          cvv: null,
          terms: null,
        },
        ipqsResult: null,
        isSubmitted: false,
        paypalPaymentError: false,
        paymentError: false,
      };
    },


    created() {
      this.initDeal();
      this.initVariant();
      this.check3dsFailure();
    },


    computed: {

      checkoutData() {
        return checkoutData;
      },

      product() {
        return this.checkoutData.product;
      },

      searchParams() {
        return new URL(location).searchParams;
      },

      dealsAllowedQuantities() {
        return [
          1, 2, 3, 4, 5,
        ];
      },

      dealsMainQuantities() {
        return {
          1: 1,
          2: 2,
          3: 2,
          4: 4,
          5: 3,
        };
      },

      dealsFreeQuantities() {
        return {
          1: 0,
          2: 0,
          3: 1,
          4: 0,
          5: 2,
        };
      },

      dealsSellout() {
        return (this.searchParams.get('sellout') || '').split(',').map(quantity => +quantity);
      },

      deals() {
        return Object.keys(this.product.prices)
          .filter(value => {
            return this.dealsAllowedQuantities.includes(+value);
          })
          .map(value => {
            return {
              ...this.product.prices[value],
              sellout: this.dealsSellout.includes(+value),
              quantity: +value,
            };
          })
          .sort((a, b) => {
            if (a.is_bestseller) return -1;
            if (a.is_popular) {
              if (b.is_bestseller) return 1;
              return -1;
            }
            if (a.quantity > b.quantity) return 1;
            if (a.quantity < b.quantity) return -1;
            return 0;
          });
      },

      dealsByValue() {
        let dealsByValue = {};

        for (let deal of this.deals) {
          dealsByValue[deal.quantity] = deal;
        }

        return dealsByValue;
      },

      productDescription() {
        return this.product.description.replace(
          /<li([^>]*)>(((?!(<\/li>)).)*)<\/li>/g,
          '<li$1><img src="' + this.$root.cdnUrl + '/assets/images/fmc5-list-check.png" /><div>$2</div></li>',
        );
      },

      dialCode() {
        const allCountries = intlTelInputGlobals.getCountryData();
        const phoneCountryCode = this.form.countryCodePhoneField;
        const country = allCountries.filter(item => item.iso2 === phoneCountryCode).shift();

        return country ? country.dialCode : '1';
      },

      imageStep() {
        return this.$root.cdnUrl + '/assets/images/fmc5-step' + this.step + '.png';
      },

      imageButtonBack() {
        return this.$root.cdnUrl + '/assets/images/fmc5-back.png';
      },

      image5star() {
        return this.$root.cdnUrl + '/assets/images/fmc5-5star.svg';
      },

      imageGuarantee() {
        return this.$root.cdnUrl + '/assets/images/fmc5-guarantee.jpg';
      },

      imageStarsLines() {
        return this.$root.cdnUrl + '/assets/images/fmc5-stars-lines.png';
      },

      imageVerifiedCheck() {
        return this.$root.cdnUrl + '/assets/images/fmc5-verified-check.svg';
      },

      textPaypalRiskFree() {
        return this.$t('checkout.paypal.risk_free');
      },

    },


    methods: {

      setDeal(deal) {
        this.form.deal = deal.quantity;
      },

      backClick() {
        this.step--;
      },

      nextClick() {
        if (this.step === 1) {
          return this.step1Submit();
        }

        if (this.step === 2) {
          return this.step2Submit();
        }

        if (this.step === 3) {
          return this.step3Submit();
        }
      },

      setBrazilAddress(res) {
        this.form.street = res.address;
        this.form.city = res.city;
        this.form.state = res.state;
      },

      checkFieldsValid(names) {
        let isValid = true;

        for (let name of names) {
          if (this.$v.form[name]) {
            this.$v.form[name].$touch();

            if (this.$v.form[name].$invalid || this.$v.form[name].$pending) {
              isValid = false;
            }
          }
        }

        return isValid;
      },

      step1Submit() {
        if (!this.checkFieldsValid(['deal', 'variant', 'installments'])) {
          return this.scrollToError();
        }

        this.step++;
      },

      step2Submit() {
        if (!this.checkFieldsValid(['fname', 'lname', 'email', 'phone', 'street', 'district', 'city', 'state', 'zipcode', 'country'])) {
          return this.scrollToError();
        }

        this.step++;
      },

      step3Submit() {
        if (!this.checkFieldsValid(['cardHolder', 'card_type', 'cardNumber', 'cardDate', 'cvv', 'document_type', 'document_number', 'terms'])) {
          return this.scrollToError();
        }

        this.creditCardSubmit();
      },

      initDeal() {
        if (this.searchParams.get('qty')) {
          const deal = +this.searchParams.get('qty');

          if (this.dealsByValue[deal]) {
            this.form.deal = deal;
          }
        }
      },

      initVariant() {
        this.form.variant = this.product.skus[0]
          ? this.product.skus[0].code
          : null;
      },

      check3dsFailure() {
        if (this.searchParams.get('3ds') === 'failure') {
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
            this.form.state = selectedProductData.state || this.form.state;
            this.form.zipcode = selectedProductData.zipcode || this.form.zipcode;
            this.form.country = selectedProductData.country || this.form.country;

            get3dsErrors()
              .then(paymentError => {
                this.paymentError = paymentError;
                setTimeout(() => this.scrollToError('#payment-error'), 1000);
              });

            this.step = 3;
          }
          catch (err) {
            
          }
        }
      },

      paypalSubmit() {
        
      },

      paypalCreateOrder() {
        const currency = !this.searchParams.get('cur') || searchParams.get('cur') === '{aff_currency}'
          ? this.checkoutData.product.prices.currency
          : this.searchParams.get('cur');

        this.setDataToLocalStorage({
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentProvider: 'paypal',
        });

        this.paypalPaymentError = false;

        return Promise.resolve()
          .then(() => {
            if (this.ipqsResult) {
              return this.ipqsResult;
            }

            const data = {
              order_amount: this.getOrderAmount(this.form.deal, this.form.isWarrantyChecked),
            };

            return ipqsCheck(data);
          })
          .then(ipqsResult => {
            this.ipqsResult = ipqsResult;
          })
          .then(() => {
            if (this.ipqsResult && this.ipqsResult.recent_abuse) {
              return setTimeout(() => this.paypalPaymentError = this.$t('checkout.abuse_error'), 1000);
            }

            return paypalCreateOrder({
              sku_code: this.form.variant,
              sku_quantity: this.form.deal,
              is_warranty_checked: this.form.isWarrantyChecked,
              cur: currency,
              offer: this.searchParams.get('offer'),
              affiliate: this.searchParams.get('affiliate'),
              ipqsResult: this.ipqsResult,
            });
          })
          .then(res => {
            if (res.paypalPaymentError) {
              this.paypalPaymentError = res.paypalPaymentError;
            }

            return res;
          });
      },

      paypalOnApprove(data) {
        this.form.paymentProvider = 'paypal';
        return paypalOnApprove(data);
      },

      creditCardSubmit() {
        if (this.isSubmitted) {
          return;
        }

        this.isSubmitted = true;
        this.paymentError = false;

        const phoneNumber = this.form.phone.replace(/[^0-9]/g, '');
        const cardNumber = this.form.cardNumber.replace(/[^0-9]/g, '');

        if (this.form.emailForceInvalid) {
          return setTimeout(() => {
            this.paymentError = this.$t('checkout.abuse_error');
            this.isSubmitted = false;
          }, 1000);
        }

        let data = {
          deal: this.form.deal,
          variant: this.form.variant,
          isWarrantyChecked: this.form.isWarrantyChecked,
          paymentProvider: this.form.paymentProvider,
          fname: this.form.fname,
          lname: this.form.lname,
          email: this.form.email,
          phone: this.form.phone,
          countryCodePhoneField: this.form.countryCodePhoneField,
          street: this.form.street,
          city: this.form.city,
          state: this.form.state,
          zipcode: this.form.zipcode,
          country: this.form.country,
        };

        this.setExtraFieldsForLocalStorage(data);
        this.setDataToLocalStorage(data);

        Promise.resolve()
          .then(() => {
            if (this.ipqsResult) {
              return this.ipqsResult;
            }

            let data = {
              order_amount: this.getOrderAmount(this.form.deal, this.form.isWarrantyChecked),
              billing_first_name: this.form.fname,
              billing_last_name: this.form.lname,
              billing_country: this.form.country,
              billing_address_1: this.form.street,
              billing_city: this.form.city,
              billing_region: this.form.state,
              billing_postcode: this.form.zipcode,
              billing_email: this.form.email,
              billing_phone: this.dialCode + phoneNumber,
              credit_card_bin: cardNumber.substr(0, 6),
              credit_card_hash: window.sha256(cardNumber),
              credit_card_expiration_month: this.form.cardDate.split('/')[0],
              credit_card_expiration_year: this.form.cardDate.split('/')[1],
              cvv_code: this.form.cvv,
            };

            return ipqsCheck(data).then(ipqsResult => {
              this.ipqsResult = ipqsResult;
            });
          })
          .then(() => {
            let data = {
              product: {
                sku: this.form.variant,
                qty: parseInt(this.form.deal, 10),
                is_warranty_checked: this.form.isWarrantyChecked,
              },
              contact: {
                phone: {
                  country_code: this.dialCode,
                  number: phoneNumber,
                },
                first_name: this.form.fname,
                last_name: this.form.lname,
                email: this.form.email,
              },
              address: {
                city: this.form.city,
                country: this.form.country,
                zip: this.form.zipcode,
                state: this.form.state,
                street: this.form.street,
              },
              card: {
                number: cardNumber,
                cvv: this.form.cvv,
                month: this.form.cardDate.split('/')[0],
                year: '20' + this.form.cardDate.split('/')[1],
              },
              ipqs: this.ipqsResult,
            };

            if (this.$root.isAffIDEmpty) {
              data.card.holder = this.form.cardHolder;
            }

            this.setExtraFieldsForCardPayment(data);

            return sendCheckoutRequest(data)
              .then(res => {
                if (res.paymentError) {
                  this.paymentError = res.paymentError;
                  this.isSubmitted = false;
                }
              });
          });
      },

    },

  };

</script>


<style lang="scss">
  
  .tpl-fmc5 {
    main.pt-4 {
      padding: 0!important;
    }
  }

</style>


<style lang="scss" scoped>
  
  .fmc5 {
    align-items: center;
    background-color: #fff;
    display: flex;
    flex-direction: column;
    font-family: Lato, "Noto Sans", sans-serif;
    position: relative;
  }

  .gray-back {
    background-color: #f1eff0;
    height: 365px;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
  }

  .fmc5 > .inside {
    display: flex;
    flex-direction: column;
    margin: 40px 0;
    position: relative;
    width: 970px;
  }

  .steps-line {
    align-self: center;
    background-color: #0089cc;
    display: flex;
    width: 74%;
  }

  .step-line {
    align-items: center;
    color: #fff;
    display: flex;
    flex-direction: column;
    font-size: 18px;
    justify-content: center;
    height: 38px;
    position: relative;
    width: 33.33%;

    &:nth-child(2) {
      background-color: #026190;
    }

    &:nth-child(3) {
      background-color: #001326;
    }
  }

  .step-line-triangle {
    border-bottom: 19px solid transparent;
    border-left: 19px solid #0089cc;
    border-top: 19px solid transparent;
    position: absolute;
    right: -19px;
    top: 0;
    z-index: 1;

    .step-line:nth-child(2) & {
      border-left-color: #026190;
    }

    .step-line:nth-child(3) & {
      display: none;
    }
  }

  .content {
    align-items: flex-start;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 7px 2px #d6d6d6;
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding: 30px 30px 50px;
  }

  .content-left {
    box-shadow: 0 0 2px 1px #e3e3e3;
    display: flex;
    flex-direction: column;
    padding-bottom: 25px;
    width: 59%;
  }

  .content-right {
    display: flex;
    flex-direction: column;
    width: 36%;
  }

  .step {
    align-items: center;
    background-color: #f7f7f7;
    display: flex;
    font-size: 28px;
    padding: 20px 20px;

    img {
      margin-right: 12px;
    }
  }

  .step-content {
    display: flex;
    flex-direction: column;
  }

  .step1 {
    display: flex;
    flex-direction: column;
  }

  #installments {
    margin: 20px 20px;
    width: auto;
  }

  .deals {
    display: flex;
    flex-direction: column;
  }

  .deal {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    margin-top: 1px;
    padding: 15px 18px;
    position: relative;

    &:first-child {
      margin-top: 0;
    }

    &.selected {
      background-color: #fff7b2;
    }

    &.sellout {
      cursor: default;
      opacity: .5;
    }
  }

  .deal-separator {
    border-bottom: 1px dashed #b8b8b8;
    left: 15px;
    position: absolute;
    right: 15px;
    top: -1px;

    .deal:first-child & {
      display: none;
    }
  }

  .deal-sellout {
    background-color: rgba(0, 0, 0, .5);
    bottom: 0;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 1;
  }

  .deal-special {
    display: flex;
    margin-bottom: 7px;
    justify-content: space-between;
  }

  .deal-content {
    align-items: flex-start;
    display: flex;
    justify-content: space-between;
  }

  .deal-left {
    align-items: center;
    display: flex;
  }

  .deal-right {
    align-items: flex-end;
    display: flex;
    flex-direction: column;
    padding-right: 7px;
  }

  .deal-bestseller, .deal-popular {
    align-items: center;
    color: #fff;
    display: flex;
    font-size: 18px;
    font-weight: 700;
    height: 28px;
    margin-left: -18px;
    padding: 0 35px 0 22px;
    position: relative;
  }

  .deal-bestseller {
    background-color: #00c749;
  }

  .deal-popular {
    background-color: #f5821e;
  }

  .deal-special-triangle {
    border-bottom: 14px solid transparent;
    border-right: 14px solid #fff;
    border-top: 14px solid transparent;
    height: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: 0;

    .deal.selected & {
      border-right-color: #fff7b2;
    }
  }

  .deal-sameas {
    font-size: 15px;
    font-weight: 700;
  }

  .deal-radio {
    margin-right: 12px;
  }

  .deal-label {
    align-items: center;
    display: flex;
    font-size: 17px;
    font-weight: 700;
  }

  .deal-discount {
    align-items: center;
    background-color: aliceblue;
    border: 1px solid #ddd;
    border-radius: 2px;
    box-shadow: 1px 1px 4px #ddd;
    color: #0089cc;
    display: flex;
    height: 30px;
    justify-content: center;
    margin-left: 12px;
    padding: 0 8px;
  }

  .deal-discount-o {
    color: #bbb;
    font-size: 9px;
    margin-left: -3px;
    margin-right: 5px;
  }

  .deal-price-one {
    align-items: center;
    display: flex;
    font-size: 14px;
    line-height: 1.5rem;
  }

  .deal-price-total {
    align-items: center;
    display: flex;
    font-size: 13px;
    line-height: 1.5rem;
  }

  .deal-free-shipping {
    font-size: 12px;
    font-weight: 600;
  }

  .step2 {
    display: flex;
    flex-direction: column;
    padding: 15px 20px 0;
  }

  .form {
    display: flex;
    flex-wrap: wrap;
  }

  #first-name-field {
    width: 48%;
  }

  #last-name-field {
    margin-left: auto;
    width: 48%;
  }

  #card-date-field {
    width: 120px;
  }

  #cvv-field {
    margin-left: 10px;
    width: 120px;
  }

  .step3 {
    display: flex;
    flex-direction: column;
    padding: 15px 20px 0;
  }

  #warranty-field-button {
    margin: 20px 0;
  }

  .terms-checkbox {
    margin: 10px 0;

    :global(.checkmark) {
      top: 3px;
    }
  }

  #payment-error {
    text-align: center;
    width: 100%;
  }

  .buttons {
    align-items: center;
    display: flex;
    margin: 20px 20px 0;
  }

  .button-back {
    background-color: #fff;
    border: 1px solid #00c749;
    border-radius: 7px;
    color: #00c749;
    cursor: pointer;
    font-size: 28px;
    font-weight: 700;
    margin-right: 18px;
    padding: 14px;
  }

  .button-next {
    align-items: center;
    animation: 1.5s pulse infinite;
    background-color: #00c749;
    border-radius: 7px;
    color: #fff;
    cursor: pointer;
    display: flex;
    flex-grow: 1;
    font-size: 28px;
    font-weight: 700;
    justify-content: center;
    height: 62px;
    position: relative;

    &.submitted {
      cursor: default;
    }

    .disabled {
      background-color: #fff;
      bottom: 0;
      left: 0;
      opacity: .5;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1;
    }

    .spinner {
      position: absolute;
      transform: scale(.6);
    }

    .hidden {
      visibility: hidden;
    }
  }

  .details {
    align-items: flex-start;
    border-bottom: 1px dashed #cbcbcb;
    display: flex;
    justify-content: space-between;
    padding-bottom: 15px;
  }

  .details-left {
    color: #000;
    display: flex;
    flex-direction: column;
    width: 62%;
  }

  .details-right {
    display: flex;
    width: 34%;
  }

  .product-name {
    font-size: 24px;
    font-weight: 700;
  }

  .product-long-name {
    font-size: 18px;
    margin-top: 2px;
  }

  .product-bestseller {
    align-items: center;
    align-self: flex-start;
    background-color: #f5821e;
    color: #fff;
    display: flex;
    font-size: 15px;
    height: 25px;
    margin-top: 5px;
    padding: 0 30px 0 10px;
    position: relative;
  }

  .product-bestseller-triangle {
    border-bottom: 13px solid transparent;
    border-right: 13px solid #fff;
    border-top: 13px solid transparent;
    height: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: 0;
  }

  .product-5star {
    margin-top: 6px;
    width: 80px;
  }

  .product-image {
    max-width: 100%;
  }

  .description {
    display: flex;
    margin-top: 10px;

    :global(ul) {
      display: flex;
      flex-direction: column;
      list-style: none;
      margin: 0;
      padding: 0;

      :global(li) {
        align-items: flex-start;
        color: #393737;
        display: flex;
        font-size: 18px;
        margin: 7px 0;
        padding: 0;

        :global(img) {
          margin-right: 12px;
          margin-top: 1px;
        }
      }
    }
  }

  .guarantee {
    background-color: #f7f7f7;
    border: 1px solid #dadada;
    border-radius: 7px;
    color: #393737;
    font-size: 15px;
    margin-top: 80px;
    padding: 20px 20px;
    text-align: center;
  }

  .guarantee-image {
    margin-bottom: 15px;
    margin-top: -88px;
  }

  .guarantee-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .guarantee-text {
    font-size: 15px;
    line-height: 1.5rem;
    margin: 5px 0;
  }

  .reviews {
    display: flex;
    flex-direction: column;
    margin-top: 50px;
  }

  .reviews-title {
    align-self: center;
    color: #393737;
    font-size: 30px;
    font-weight: 600;
    position: relative;
  }

  .reviews-title-left, .reviews-title-right {
    border-bottom: 1px dashed #393737;
    position: absolute;
    width: 140px;
    top: 15px;
  }

  .reviews-title-left {
    left: -160px;
  }

  .reviews-title-right {
    right: -160px;
  }

  .reviews-block {
    align-items: center;
    display: flex;
    justify-content: center;
    margin: 60px 0 50px 0;
  }

  .rate-box {
    background-color: #3574d7;
    color: #fff;
    display: flex;
    flex-direction: column;
    width: 115px;
  }

  .rate-box-value {
    align-items: center;
    display: flex;
    font-size: 40px;
    font-weight: 600;
    height: 85px;
    justify-content: center;
  }

  .rate-box-outof {
    align-items: center;
    background-color: #003854;
    display: flex;
    font-size: 18px;
    font-weight: 600;
    height: 32px;
    justify-content: center;
  }

  .stars-lines {
    margin: 0 32px;
  }

  .reviews-overall {
    align-items: center;
    color: #393737;
    display: flex;
    flex-direction: column;
    font-size: 24px;
    line-height: 1.625rem;
    font-weight: 600;
    text-align: center;
    width: 150px;

    img {
      margin-top: 7px;
      width: 100px;
    }
  }

  .reviews-percent {
    align-items: center;
    color: #393737;
    display: flex;
    flex-direction: column;
    margin: 0 25px;
    text-align: center;
    width: 170px;
  }

  .reviews-percent-value {
    font-size: 24px;
    font-weight: 600;
  }

  .reviews-percent-text {
    font-size: 14px;
    font-weight: 600;
  }

  .reviews-items {
    display: flex;
    flex-direction: column;
  }

  .review {
    border-top: 1px dashed #cbcbcb;
    color: #393737;
    display: flex;
    flex-direction: column;
    padding: 20px 0;
  }

  .review-5star {
    display: flex;
    flex-direction: column;
    margin-top: 5px;
    overflow: hidden;

    img {
      width: 100px;
    }
  }

  .review-text {
    font-size: 18px;
    font-style: italic;
    line-height: 1.5rem;
    margin-top: 8px;
  }

  .review-name {
    font-size: 18px;
    font-style: italic;
    font-weight: 600;
    line-height: 1.5rem;
    margin-top: 12px;
  }

  .review-verified {
    align-items: flex-start;
    display: flex;
    font-size: 16px;
    margin-top: 5px;

    img {
      margin-right: 5px;
      margin-top: 5px;
      width: 15px;
    }
  }

  @keyframes pulse {
    0% {
      transform: scale(.9);
    }
    50% {
      transform: scale(1);
    }
    100% {
      transform: scale(.9);
    }
  }

  .fade-enter-active {
    transition: opacity ease .5s;
  }

  .fade-enter, .fade-leave-to {
    opacity: 0;
  }

  .radio-button {
    background-color: #fff;
    border: 1px solid #000;
    border-radius: 50%;
    height: 15px;
    position: relative;
    width: 15px;

    div {
      background-color: #000;
      border-radius: 50%;
      height: 7px;
      left: 3px;
      position: absolute;
      top: 3px;
      width: 7px;
    }
  }

  .input-container.variant-1,
  .phone-input-container.variant-1,
  .select.variant-1 {
    margin: 10px 0;

    :global(.label) {
      display: none;
    }

    :global(input),
    :global(select) {
      animation: none!important;
      background-color: #fff;
      border-color: #bbb;
      border-radius: 3px;
      color: #000;
      font-family: Lato, "Noto Sans", sans-serif;
      font-size: 18px;
      height: 44px;
      padding: 0 15px;

      &:focus {
        border-color: #bbb;
        box-shadow: none;
      }
    }

    :global(.date-picker-manual__input) {
      height: 44px;

      :global(.placeholder) {
        color: gray;
        font-size: 18px;
      }
    }

    :global(.el-loading-spinner) {
      margin-top: -12px;
    }

    :global(.error) {
      font-size: 12px;
      color: #dc3545;
      margin: 4px 16px;
    }
  }



</style>
