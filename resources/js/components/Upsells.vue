<template>
    <div class="upsells-component">
        <div class="upsells-component__top">
            <div class="upsells-component__step">
                {{ textFirstStep }}
            </div>
            <div
                class="upsells-component__step"
                :class="{
                  'active': activeTab === 'second'
                }">
                {{ textSecondStep }}
            </div>
            <div
                class="upsells-component__step"
                :class="{
                  'active': activeTab === 'third'
                }">
                {{ textThirdStep }}
            </div>
        </div>
        <template v-if="activeTab === 'second'">
            <div class="upsells-component__content">
                <transition
                  name="component-fade"
                  mode="out-in"
                >
                  <component
                    v-if="upsellsObj.length"
                    v-bind:is="view"
                    @addAccessory="addAccessory"
                    :discount="upsellsObj && upsellsObj[getEntity] && upsellsObj[getEntity].discount_percent || 0"
                    :id="upsellsObj && upsellsObj[getEntity] && upsellsObj[getEntity].product_id || ''"
                  />
                </transition>
                <p class="no">
                  <a @click="accessoryStep++">
                    {{ textCancel }}
                  </a>
                </p>
            </div>
        </template>
        <template v-if="activeTab === 'third'">
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
                    :quantity="it.quantity"
                    :final-price="it.finalPrice"
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
  import UpsellsItem from './common/UpsellsItem';
  import Step1 from './upsells/Step1';
  import Step3 from './upsells/Step3';
  import StepWithOneItem from './upsells/StepWithOneItem';
  import { fade } from '../utils/common';
  import { goTo } from '../utils/goTo';
  import { groupBy } from '../utils/groupBy';
  import { send1ClickRequest, paypalCreateOrder, paypalOnApprove } from '../utils/upsells';
  import upsellsMixin from '../mixins/upsells';
  import { getTotalPrice } from '../services/upsells';

  export default {
    name: 'upsells',
    mixins: [
      upsellsMixin,
    ],

    components: {
      UpsellsItem,
      Step1,
      Step3,
      StepWithOneItem,
    },

    data() {
      return {
        total: 0,
        view: 'Step1',
        activeTab: 'second',
        accessoryStep: 0,
        accessoryList: [],
        product: js_data.product,
        upsellsObj: js_data.product.upsells,
        orderCustomer: js_data.order_customer,
        upsellsAsProdsList: [],
        paymentError: '',
        isSubmitted: false,
        isLoading: false,
      };
    },

    beforeCreate() {
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
                getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
                  .then((total) => {
                    this.total = total;
                    this.activeTab = 'third';
                  })
                  .catch(() => {
                    this.activeTab = 'third';
                  });
              } else {
                this.redirect();
              }

              setTimeout(() => fade('in', 250, node, true));
            });
        }

        if (val === 0) {
          this.view = 'Step1';
        }

        if (val === this.upsellsObj.length - 1) {
          this.view = 'Step3';
        }

        else {
          this.view = 'StepWithOneItem';
        }
      }
    },

    computed: {
      formattedAccessoryList() {
        return this.accessoryList.map(({ quantity, id }) => ({
          quantity,
          id,
        }))
      },

      textFirstStep: () => t('upsells.step_1.title'),
      textSecondStep: () => t('upsells.step_2.title'),
      textThirdStep: () => t('upsells.step_3.title'),
      textCancel: () => t('upsells.cancel'),
      textOriginalOrder: () => t('upsells.original_order'),
      textAccessoryCart: () => t('upsells.accessory_cart'),
      textTotalAccessoryOrder: () => t('upsells.accessory_order'),
      textBuyNow: () => t('upsells.paypal_button_text'),
      textQuantity: () => t('upsells.quantity'),
      textBuyAccessories: () => t('upsells.step_3.buy_accessories'),
      textPaymentError: () => t('upsells.step_3.payment_error'),

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
          selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};
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
      submit() {
        if (this.isSubmitted) {
          return;
        }

        this.paymentError = '';
        this.isSubmitted = true;

        const data = {
          order: this.getOriginalOrderId,
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

        getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
          .then((total) => {
            this.total = total;
        })
      },

      redirect() {
        goTo(`/thankyou/?`);
      },
    },
  };
</script>

<style lang="scss">
    $gray: #dadada;

    .upsells-component {
        background-color: #fff;
        border: 1px solid $gray;

        &__top {
            display: flex;
            background-color: $gray;
        }

        &__step {
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            width: calc(100% / 3);
            text-transform: uppercase;
            color: #a5a5a6;

            &.active {
                background-color: #272727;
                color: #fff;
            }
        }

        &__content {
            padding: 40px 80px;

            .green-up {
                color: #0d840d;
            }

            h4 {
                font-size: 20px;
                text-align: center;

                .congrats {
                    color: #d4513a;
                }
            }

            h5 {
                font-size: 25px;
                text-align: center;
                margin-top: 0;
            }
        }

        &__item {
            display: flex;

            .benefits {
                list-style-type: none;
                width: 60%;
                font-size: 20px;
                flex-grow: 1;

                li {
                    margin-bottom: 2px;
                }
            }

            .image {
                max-width: 300px;

                img {
                    max-width: 150px;
                    width: 100%;
                    height: auto;
                }
            }
        }

        &__bot {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;

            .green-button-animated {
                max-width: 350px;
                width: 100%;
                height: auto;
            }
        }

        .no {
            margin-top: 20px;
            text-align: center;

            a {
                cursor: pointer;
                text-decoration: none;
                color: #3f75b5;
            }
        }

        &__finish {
            padding: 15px;

            .original-order {
                text-align: left;
                margin-bottom: 0;
            }

            .upsells-item {
                margin-bottom: 15px;
            }

            .total-price {
                text-align: right;
                margin: 30px 0;
            }

            .buy-block {
                display: flex;
                justify-content: center;

                .buy-button {
                    height: 60px;
                    width: 100%;
                    max-width: 780px;
                }
            }
        }

        #payment-error {
          font-size: 17px;
          text-align: center;
        }

        .submit-button {
          margin-left: auto;
          margin-right: auto;
          max-width: 670px;

          &.green-button-animated {
            font-size: 26px;
            height: 79px;
          }
        }

        @media screen and (max-width: 992px) {
            &__bot {
                flex-direction: column;
                align-items: center;
            }

            &__step {
                display: none;

                &.active {
                    display: flex;
                }
            }

            &__content {
                padding: 5px;
            }
        }
    }
</style>
