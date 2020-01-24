import '../resourses/polyfills';
import carousel from 'vue-owl-carousel';
import { stateList } from '../resourses/state';
import emc1Validation from '../validation/emc1-validation'
import { paypalCreateOrder, paypalOnApprove } from '../utils/emc1';
import { preparePurchaseData } from '../utils/checkout';
import { t } from '../utils/i18n';
import { scrollTo } from '../utils/common';
import Installments from '../components/common/extra-fields/Installments';
import { getCountOfInstallments } from '../utils/installments';
import * as extraFields from '../mixins/extraFields';
import notification from '../mixins/notification';
import queryToComponent from '../mixins/queryToComponent';
import purchasMixin from '../mixins/purchas';
import blackFriday from '../mixins/blackFriday';
import christmas from '../mixins/christmas';
import { ipqsCheck } from '../services/ipqs';
import { queryParams } from  '../utils//queryParams';
import globals from '../mixins/globals';
import wait from '../utils/wait';


js_deps.wait(['vue', 'element', 'intl_tel_input'], () => {
  require('../bootstrap');

  const promo = new Vue({
    el: "#promo",

    mixins: [
      globals,
      notification,
      queryToComponent,
      extraFields.appMixin,
      extraFields.tplMixin,
      purchasMixin,
      blackFriday,
      christmas,
    ],

    data() {
      return {
        showPreloader: js_query_params.preload === '{preload}' || +js_query_params.preload === 3,
        isFormShown: false,
        selectedPlan: null,
        ipqsResult: null,
        paypalPaymentError: '',
        stateList: (stateList[js_data.country_code] || []).map((it) => ({
          value: it,
          text: it,
          label: it,
        })),
        form: {
          isWarrantyChecked: false,
          countryCodePhoneField: js_data.country_code,
          deal: null,
          variant: null,
          paymentProvider: null,
          fname: null,
          lname: null,
          //dateOfBirth: '',
          email: null,
          phone: null,
          street: null,
          city: null,
          state: null,
          zipcode: null,
          country: js_data.country_code,
          cardHolder: null,
          cardNumber: null,
          cardDate: null,
          cvv: null,
          terms: null,
        },
        slideForm: null,
        carouselFormHeight: 'auto',
        slideFormStep: 0,
        slideFormSteps: 0,
        isShownFooter: true,
        isShownJumbotron: true,
      };
    },

    validations: emc1Validation,

    components: {
      Installments,
      carousel,
    },

    created() {
      if (this.queryParams['3ds'] === 'failure') {
        try {
          const selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};

          this.form.deal = selectedProductData.deal || this.form.deal;
          this.form.variant = selectedProductData.variant || this.form.variant;
          this.form.isWarrantyChecked = selectedProductData.isWarrantyChecked || this.form.isWarrantyChecked;
          this.form.paymentProvider = selectedProductData.paymentProvider || this.form.paymentProvider;
          this.form.fname = selectedProductData.fname || this.form.fname;
          this.form.lname = selectedProductData.lname || this.form.lname;
          //this.form.dateOfBirth = selectedProductData.dateOfBirth || this.form.dateOfBirth;
          this.form.email = selectedProductData.email || this.form.email;
          this.form.phone = selectedProductData.phone || this.form.phone;
          this.form.countryCodePhoneField = selectedProductData.countryCodePhoneField || this.form.countryCodePhoneField;
          this.form.street = selectedProductData.street || this.form.street;
          this.form.city = selectedProductData.city || this.form.city;
          this.form.state = selectedProductData.state || this.form.state;
          this.form.zipcode = selectedProductData.zipcode || this.form.zipcode;
          this.form.country = selectedProductData.country || this.form.country;
          this.setSelectedPlan(+this.form.deal);
          this.isFormShown = true;
        }
        catch (err) {

        }
      }

      if (js_query_params.tpl === 'vmp41') {
        document.body.classList.add('tpl-vmp41');
        this.slideForm = false;
      }

      if (js_query_params.tpl === 'vmp42') {
        document.body.classList.add('tpl-vmp42');
        this.setStickyFooter();
        this.slideForm = true;
      }
    },

    mounted() {
      const qty = +this.queryParams.qty;
      const deal = this.purchase.find(({ totalQuantity }) => qty === totalQuantity);

      if (deal) {
        this.setSelectedPlan(qty);

        setTimeout(() => {
          this.scrollTo('.j-variant-section');
        }, 500);
      }

      wait(
        () => !this.showPreloader,
        () => {
          this.$nextTick(() => {
            this.slideFormSteps = [].slice.call(document.querySelectorAll('.promo__step'));
          });
        },
      );
    },

    computed: {

      isRTL() {
        return !!document.querySelector('html[dir="rtl"]');
      },

      isShowVariant() {
        return this.variantList.length > 1 && (!js_query_params.variant || js_query_params.variant !== '0');
      },

      countriesList() {
        return js_data.countries
          .map(name => {
            const label = t('country.' + name);

            return {
              value: name,
              lc: label.toLowerCase(),
              text: label,
              label,
            };
          })
          .sort((a, b) => {
            if (a.lc > b.lc) return 1;
            if (a.lc < b.lc) return -1;
            return 0;
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

      purchase() {
        const variant = this.form.variant || (js_data.product.skus[0] && js_data.product.skus[0].code) || null;

        return preparePurchaseData({
          purchaseList: this.productData.prices,
          product_name: this.productData.product_name,
          installments: this.form.installments,
          image: this.productData.image[0],
          variant,
        });
      },

      productImages() {
        return js_data.product.skus && js_data.product.skus[0] && js_data.product.skus[0].quantity_image || {};
      },

      promoPriceText() {
        const prices = js_data.product.prices;

        switch (this.form.installments) {
          case 1:
            return prices[1].value_text;
          case 3:
            return prices[1].installments3_value_text;
          case 6:
            return prices[1].installments6_value_text;
        }

        return 0;
      },

      promoOldPrice() {
        const prices = js_data.product.prices;

        switch (this.form.installments) {
          case 1:
            return prices[1].old_value_text;
          case 3:
            return prices[1].installments3_old_value_text;
          case 6:
            return prices[1].installments6_old_value_text;
        }

        return 0;
      },

      warrantyPriceText() {
        const prices = js_data.product.prices;
        const quantity = this.form.deal || 1;

        switch (this.form.installments) {
          case 1:
            return prices[quantity].warranty_price_text;
          case 3:
            return prices[quantity].installments3_warranty_price_text;
          case 6:
            return prices[quantity].installments6_warranty_price_text;
        }

        return 0;
      },

      countOfInstallments() {
        return getCountOfInstallments(this.form.installments);
      },

      productData () {
        return js_data.product
      },

      skusList() {
        return Array.isArray(js_data.product.skus)
          ? js_data.product.skus
          : [];
      },

      hasTimer() {
        return document.getElementById('timer-component');
      },

      textPromoDiscount() {
        const discount = js_data.product.prices[1].discount_percent;
        return t('checkout.promo.buy_now', { amount: discount });
      },

      textDiscountStarter: () => t('checkout.discount_starter'),

    },

    methods: {
      scrollTo: scrollTo,

      paypalSubmit() {
        
      },

      paypalCreateOrder () {
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
              return setTimeout(() => {
                this.paypalPaymentError = t('checkout.abuse_error');
              }, 1000);
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

      setSelectedPlan(deal) {
        this.isShownFooter = false;
        this.selectedPlan = deal;
        this.form.deal = deal;

        if (!this.isShowVariant) {
          this.form.variant = this.skusList[0].code;
        }

        if (this.slideForm) {
          this.nextStep();

          this.$nextTick(() => {
            this.isShownJumbotron = false;
          });
        } else {
          this.scrollTo('.j-complete-order');
        }
      },

      setSelectedVariant(variant) {
        this.form.variant = variant;

        if(this.slideForm) {
            this.nextStep();
        }else{
            this.scrollTo('.j-complete-order');
        }
      },

      activateForm() {
        this.isFormShown = true;
        this.scrollTo('.j-payment-form');

        if(this.slideForm) {
            this.$nextTick(()=>{this.getFormHeight()});
            setTimeout(()=>{
                  this.scrollTo('.j-payment-form');
            }, 300)
        }else{
            this.scrollTo('.j-payment-form')
        }
      },

      nextStep() {
        this.slideFormStep++;
        this.stepAnimation();
        this.isShownJumbotron = false;
        if(this.slideFormStep > this.slideFormSteps.length) {this.slideFormStep = this.slideFormSteps.length};
        this.$nextTick(()=>{this.getFormHeight()});
      },

      prevStep() {
          this.slideFormStep--;
          this.stepAnimation();

          if(this.slideFormStep === 0) {
            this.selectedPlan = null;
            this.isShownJumbotron = true;
          }

          if(this.slideFormStep < 1) {this.slideFormStep = 0};
          this.$nextTick(()=>{this.getFormHeight()});
      },

      stepAnimation () {
          this.slideFormSteps.forEach((item) => {
            const stepPosition = (!this.isRTL ? '-' : '') + `${this.slideFormStep * 100}%`;
            item.style.transform = `translate(${stepPosition})`
          })
      },

      getFormHeight() {
        if (this.slideFormSteps[this.slideFormStep]) {
          this.carouselFormHeight = `${this.slideFormSteps[this.slideFormStep].offsetHeight}px`;
        }
      },

      setStickyFooter() {
        let showStickyFooter = false;

        if (this.isShowVariant && this.slideFormStep === 1) {
          document.body.classList.add('slide-grey-back');
          showStickyFooter = true;
        } else {
          document.body.classList.remove('slide-grey-back');
        }

        if ((this.isShowVariant && this.slideFormStep === 2) || (!this.isShowVariant && this.slideFormStep === 1)) {
          if (!this.isFormShown) {
            showStickyFooter = true;
          }
        }

        if (showStickyFooter) {
          document.body.classList.add('with-sticky-footer');
        } else {
          document.body.classList.remove('with-sticky-footer');
        }
      },
    },

    watch: {
      '$root.paymentMethods'() {
        setTimeout(() => {
          this.getFormHeight();
        }, 100);
      },
      slideFormStep() {
        this.setStickyFooter();
      },
      isFormShown() {
        this.setStickyFooter();
      },
    },
  });

});
