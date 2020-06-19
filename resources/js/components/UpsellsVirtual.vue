<template>  
  <div class="upsells-component">
    <template v-if="activeTab === 1">
      <div class="upsells-component__content">
        <transition
          name="component-fade"
          mode="out-in"
        >
          <component
            v-if="upsellsObj.length"
            :is="view"
            :isRootLoading="isRootLoading"
            :nextAccessoryStep="nextAccessoryStep"
            @addAccessory="addAccessory"
            :upsellDiscount="upsellDiscount"
            :upsellDiscountOffered="upsellDiscountOffered"
            :setUpsellDiscountAdded="setUpsellDiscountAdded"
            :discount="upsellsObj && upsellsObj[getEntity] && upsellsObj[getEntity].discount_percent || 0"
            :id="upsellsObj && upsellsObj[getEntity] && upsellsObj[getEntity].product_id || ''"
            :accessoryStep="accessoryStep"
            :accessoryList="accessoryList"
          />
        </transition>
      </div>
    </template>

    <template v-if="activeTab === 2">
      <div class="upsells-component__finish">
          <h3 class="original-order">{{ textOriginalOrder }}</h3>
          <UpsellsItem
            :image-url="product.image[0]"
            :name="product.long_name"
            :subtotal="orderCustomer.productsText[0].price_text"
            :warranty="getOriginalOrder.isWarrantyChecked || getOriginalOrder.warranty
              ? orderCustomer.productsText[0].warranty_price_text
              : null"
            :benefitList="[
              `${textQuantity}: ${getOriginalOrder.quantity || getOriginalOrder.deal}`,
            ]"
          />
          <template v-if="accessoryList.length">
            <h3 class="accessory-cart">
              {{ textAccessoryCart }}
            </h3>

            <UpsellsItem
              @deleteAccessory="deleteAccessory"
              v-for="(it, idx) in accessoryList"
              :image-url="it.imageUrl"
              :idx="idx"
              :id="it.id"
              :key="idx"
              :benefitList="[
                it.name,
                `${textQuantity}: ${it.quantity}`,
              ]"
              :item-data="it"
              :price="it.price"
              :upsellDiscount="upsellDiscount"
              :quantity="it.quantity"
              :final-price="it.finalPrice"
              :price-d="it.price30dFormatted"
              :withRemoveButton="true"
            />

            <p class="total-price">
              {{ textTotalAccessoryOrder }}: {{ total }}
            </p>

            <p
              v-if="paymentError"
              id="payment-error"
              class="error-container"
              v-html="paymentError"></p>

            <paypal-button
              v-if="paymentProvider === 'paypal'"
              class="submit-button"
              :createOrder="paypalCreateOrder"
              :onApprove="paypalOnApprove"
            >
              {{ textBuyNow }}
            </paypal-button>

            <green-button
              v-else
              class="submit-button"
              :isLoading="isSubmitted"
              @click="submit">
                {{ textBuyAccessories }}
            </green-button>
          </template>
      </div>
    </template>
  </div>
</template>

<script>
  import wait from '../utils/wait';
  import { t } from '../utils/i18n';
  import Step1Vrtl from './upsells/vrtl/Step1Vrtl';
  import Step3Vrtl from './upsells/vrtl/Step3Vrtl';
  import UpsellsItem from './common/UpsellsItem';
  import PaypalButton from './common/PaypalButton';
  import GreenButton from './common/GreenButton';
  import { fade } from '../utils/common';
  import { goTo } from '../utils/goTo';
  import { groupBy } from '../utils/groupBy';
  import { send1ClickRequest, paypalCreateOrder, paypalOnApprove } from '../utils/upsells';
  import upsellsMixin from '../mixins/upsells';
  import { getTotalPrice } from '../services/upsells';
  import logger from '../mixins/logger';

  export default {
    name: 'UpsellsVirtual',

    components: {
      Step1Vrtl,
      Step3Vrtl,
      UpsellsItem
    },

    data: () => ({
      total: 0,
      view: 'Step1Vrtl',
      activeTab: 1,
      accessoryStep: 0,
      accessoryList: [],
      isRootLoading: false,
      upsellDiscount: false,
      upsellDiscountOffered: false,
      product: js_data.product,
      upsellsObj: js_data.product.upsells,
      orderCustomer: js_data.order_customer,
      upsellsAsProdsList: [],
      paymentError: '',
      isSubmitted: false,
      isLoading: false,
    }),

    beforeCreate() {
      window.serverData = {};

      localStorage.removeItem('subOrder');

      if (js_data.product.upsells.length === 0 || (window.performance && performance.navigation && performance.navigation.type == 1)) {
        return goTo('/thankyou');
      }

      wait(
        () => document.readyState === 'complete',
        () => setTimeout(() => window.location = '#', 500),
      );
    },

    watch: {
      accessoryStep(val) {
        if (val === this.upsellsObj.length) {
          const node = document.querySelector('.upsells-component__content');
          fade('out', 250, node, true)
            .then(() => {
              if (this.accessoryList.length !== 0) {
                if (!this.totalAccessoryPrice) {
                  this.log_data('UPSELLS: total=0 - watch', {
                    force: true,
                    url: location.href,
                    accessoryStep: val,
                    serverData: window.serverData,
                    subOrder: localStorage.getItem('subOrder'),
                    totalAccessoryPrice: this.totalAccessoryPrice,
                    formattedAccessoryList: this.formattedAccessoryList,
                    accessoryList: this.accessoryList,
                    upsellsObj: this.upsellsObj,
                    ua: navigator.userAgent,
                  });
                }

                this.isRootLoading = true;

                getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
                  .then((total) => {
                    this.total = total;
                    this.activeTab = 2;
                    this.isRootLoading = false;

                    if (val === 0) {
                      this.view = 'Step1Vrtl';
                    } else {
                      this.view = 'Step3Vrtl';
                    }
                  })
                  .catch(() => {
                    this.activeTab = 2;
                    this.isRootLoading = false;

                    if (val === 0) {
                      this.view = 'Step1Vrtl';
                    } else {
                      this.view = 'Step3Vrtl';
                    }
                  });
              } else {
                this.isRootLoading = true;
                this.redirect();
              }

              setTimeout(() => fade('in', 250, node, true));
            });
        } else {
          if (val === 0) {
            this.view = 'Step1Vrtl';
          } else {
            this.view = 'Step3Vrtl';
          }
        }
      }
    },

    computed: {
      textOriginalOrder: () => t('upsells.original_order'),
      textTotalAccessoryOrder: () => t('upsells.accessory_order'),
      textBuyAccessories: () => t('upsells.step_3.buy_accessories'),
      textBuyNow: () => t('upsells.paypal_button_text'),
      textQuantity: () => t('upsells.quantity'),
      textAccessoryCart: () => t('upsells.accessory_cart'),

      formattedAccessoryList() {
        return this.accessoryList.map(({ quantity, id }) => ({
          quantity,
          id,
        }))
      },

      getEntity() {
        if (this.accessoryStep > this.upsellsObj.length) {
          return this.upsellsObj.length;
        } else {
          return this.accessoryStep;
        }
      },

      getOriginalOrder() {
        let selectedProductData = {};

        try {
          const data = localStorage.getItem('selectedProductData') || localStorage.getItem('saved_form');
          selectedProductData = JSON.parse(data) || {};
        }
        catch (err) {

        }

        return selectedProductData;
      },

      getOriginalOrderPrice() {
        const deal = this.getOriginalOrder.quantity || this.getOriginalOrder.deal || 1;
        return js_data.product.prices[deal].value;
      },

      getOriginalOrderId() {
        return localStorage.getItem('odin_order_id');
      },

      totalAccessoryPrice() {
        return this.accessoryList
          .map(it => it.finalPricePure)
          .reduce((acc, item) => acc + item, 0);
      },

      paymentProvider() {
        return this.getOriginalOrder.paymentProvider || this.getOriginalOrder.payment_provider;
      },
    },

    methods: {
      nextAccessoryStep () {        
        if (this.accessoryStep === 0 && !this.upsellDiscountOffered) {
          this.upsellDiscountOffered = true;
          this.isRootLoading = true;

          setTimeout(() => {
            this.isRootLoading = false;
            window.scrollTo(0,0);
          }, 300);
        } else {
          this.accessoryStep = this.accessoryStep + 1;
          window.scrollTo(0,0);
        }
      },

      setUpsellDiscountAdded () {
        this.upsellDiscount = true;
      },

      submit() {
        if (this.isSubmitted) {
          return;
        }

        this.paymentError = '';
        this.isSubmitted = true;

        const data = {
          order: this.getOriginalOrderId,
          is_discount: this.upsellDiscount,
          upsells: this.accessoryList.map(upsell => ({
            id: upsell.id,
            qty: upsell.quantity,
          })),
        };

        send1ClickRequest(data, this.accessoryList, this.paymentProvider)
          .then(res => {
            if (res.paymentError) {
              this.paymentError = res.paymentError;
              this.isSubmitted = false;
            }
          });
      },

      paypalCreateOrder() {
        return paypalCreateOrder({
          xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
          sku_code: this.getOriginalOrder.variant,
          sku_quantity: this.getOriginalOrder.quantity || this.getOriginalOrder.deal,
          is_warranty_checked: false,
          order: this.getOriginalOrderId,
          page_checkout: document.location.href,
          offer: js_query_params.offer || null,
          affiliate: js_query_params.affiliate || null,
          is_discount: this.upsellDiscount,
          upsells: groupBy(this.accessoryList, 'id', 'quantity')
        })
      },

      paypalOnApprove: paypalOnApprove,

      addAccessory(accessory) {
        this.accessoryStep++;
        this.accessoryList.push(accessory);
      },

      deleteAccessory(indexForDeleting) {
        this.accessoryList.splice(indexForDeleting, 1);

        if (this.accessoryList.length === 0) {
          this.redirect();
          return;
        }

        if (!this.totalAccessoryPrice) {
          this.log_data('UPSELLS: total=0 - delete', {
            force: true,
            url: location.href,
            serverData: window.serverData,
            subOrder: localStorage.getItem('subOrder'),
            totalAccessoryPrice: this.totalAccessoryPrice,
            formattedAccessoryList: this.formattedAccessoryList,
            accessoryList: this.accessoryList,
            upsellsObj: this.upsellsObj,
            ua: navigator.userAgent,
          });
        }

        getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
          .then((total) => {
            this.total = total;
        })
      },

      redirect() {
        goTo(`/thankyou/?`);
      }
    }
  };
</script>
