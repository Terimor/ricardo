<template>
    <div class="upsells-component">
        <div class="upsells-component__top">
            <div class="upsells-component__step">
              Step 1: Order Page
            </div>
            <div
                class="upsells-component__step"
                :class="{
                  'active': activeTab === 'second'
                }">
                Step 2: Special Offer
            </div>
            <div
                class="upsells-component__step"
                :class="{
                  'active': activeTab === 'third'
                }">
                Step 3: Confirmation
            </div>
        </div>
        <template v-if="activeTab === 'second'">
            <div class="upsells-component__content">
                <transition
                  name="component-fade"
                  mode="out-in"
                >
                  <component
                    v-bind:is="view"
                    @addAccessory="addAccessory"
                    :discount="upsellsObj
                      && upsellsObj[getEntity]
                      && upsellsObj[getEntity].discount_percent"
                    :id="upsellsObj
                      && upsellsObj[getEntity]
                      && upsellsObj[getEntity].product_id"
                  />
                </transition>
                <p class="no">
                  <a @click="accessoryStep++">
                    No thanks...
                  </a>
                </p>
            </div>
        </template>
        <template v-if="activeTab === 'third'">
            <div class="upsells-component__finish">
                <h3 class="original-order">Your original order</h3>
                <UpsellsItem
                  :image-url="product.image[0]"
                  :name="product.long_name"
                  :subtotal="product.prices[getOriginalOrder.quantity].value_text"
                  :warranty="getOriginalOrder.isWarrantyChecked
                    ? getOriginalOrder.prices.warranty_price_text
                    : null"
                  :benefitList="[
                    `Quantity: ${getOriginalOrder.quantity}`,
                  ]"
                />
                <template v-if="accessoryList.length">
                  <h3 class="accessory-cart">
                    Your accessory cart
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
                      `Quantity: ${it.quantity}`,
                    ]"
                    :item-data="it"
                    :price="it.price"
                    :quantity="it.quantity"
                    :final-price="it.finalPrice"
                    :withRemoveButton="true"
                  />
                  <p class="total-price">
                    Total accessory order: {{ total }}
                  </p>
                  <paypal-button
                    :createOrder="paypalCreateOrder"
                    :onApprove="paypalOnApprove"
                    :$v="true"
                  >
                    Buy Now Risk Free PAYPAL
                  </paypal-button>
                </template>
            </div>
        </template>
    </div>
</template>

<script>
  import UpsellsItem from './common/UpsellsItem';
  import Step1 from './upsells/Step1';
  import Step3 from './upsells/Step3';
  import StepWithOneItem from './upsells/StepWithOneItem';
  import { fade } from '../utils/common';
  import { goTo } from '../utils/goTo';
  import { groupBy } from '../utils/groupBy';
  import { paypalCreateOrder, paypalOnApprove } from '../utils/upsells';
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
        product: upsellsData.product,
        upsellsObj: upsellsData.product.upsells,
        upsellsAsProdsList: [],
        isLoading: false,
      };
    },

    mounted() {
      if(this.upsellsObj.length === 0) {
        this.redirect();
      }
      localStorage.removeItem('subOrder');
    },

    watch: {
      accessoryStep(val) {
        if (val === this.upsellsObj.length) {
          const node = document.querySelector('.upsells-component__content');
          fade('out', 250, node, true)
            .then(() => {
              getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
              .then((total) => {
                this.total = total;
              })
                .finally(() => {
                  this.activeTab = 'third';
                })

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

      getEntity() {
        if (this.accessoryStep > this.upsellsObj.length) {
          return this.upsellsObj.length;
        } else {
          return this.accessoryStep;
        }
      },

      getOriginalOrder() {
        return JSON.parse(localStorage.getItem('selectedProductData'));
      },

      getOriginalOrderPrice() {
        return this.getOriginalOrder.prices && this.getOriginalOrder.prices.value;
      },

      getOriginalOrderId() {
        return localStorage.getItem('odin_order_id');
      },

      totalAccessoryPrice() {
        return this.accessoryList
          .map(it => it.finalPricePure)
          .reduce((acc, item) => acc + item, 0);
      },
    },

    methods: {
      paypalCreateOrder() {
        return paypalCreateOrder({
          xsrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
          sku_code: this.getOriginalOrder.variant,
          sku_quantity: this.getOriginalOrder.quantity,
          is_warranty_checked: false,
          order_id: this.getOriginalOrderId,
          page_checkout: document.location.href,
          offer: new URL(document.location.href).searchParams.get('offer'),
          affiliate: new URL(document.location.href).searchParams.get('affiliate'),
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

        getTotalPrice(this.formattedAccessoryList, this.totalAccessoryPrice)
          .then((total) => {
            this.total = total;
        })

        if (this.accessoryList.length === 0) {
          this.redirect();
        }
      },

      redirect() {
        goTo(`/thankyou/?order=${this.getOriginalOrderId}`);
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
