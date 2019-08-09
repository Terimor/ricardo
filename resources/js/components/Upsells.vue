<template>
    <div class="upsells-component">
        <div class="upsells-component__top">
            <div class="upsells-component__step">Step 1: Order Page</div>
            <div
                class="upsells-component__step"
                :class="{ 'active': activeTab === 'second'}">
                Step 2: Special Offer
            </div>
            <div
                class="upsells-component__step"
                :class="{ 'active': activeTab === 'third'}">
                Step 3: Confirmation
            </div>
        </div>

        <template v-if="activeTab === 'second'">
            <div class="upsells-component__content">
                <transition name="component-fade" mode="out-in">
                    <component
                        v-bind:is="view"
                        @addAccessory="addAccessory"
                        :benefit-list="[
                            'One',
                            'Two',
                            'Some text',
                        ]"
                        :viewProps="viewProps"
                    />
                </transition>



                <p class="no"><a @click="accessoryStep++">No thanks...</a></p>

            </div>
        </template>
        <template v-if="activeTab === 'third'">
            <div class="upsells-component__finish">
                <h3 class="original-order">Your original order</h3>
                <UpsellsItem :image-url="'/images/aircool.jpg'" name="EcoFuel" :benefitList="['Quantity: 1']"/>
                <h3 class="accessory-cart">Your accessory cart</h3>
                <UpsellsItem
                    @deleteAccessory="deleteAccessory"
                    v-for="(it, idx) in accessoryList"
                    :imageUrl="it.imageUrl"
                    :idx="idx"
                    :key="it.name + idx"
                    :benefitList="[it.name, `Quantity: ${it.quantity}`, `Subtotal: $${it.quantity * it.price}`]"
                    :withRemoveButton="true"/>

                <p class="total-price">Total accessory order ${{totalPrice.toLocaleString()}}</p>

                <div class="buy-block">
                    <green-button class="buy-button" @click="goto('/thankyou')">Buy Accessories</green-button>
                </div>

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

  export default {
    name: 'upsells',
    components: {
      UpsellsItem,
      Step1,
      Step3,
      StepWithOneItem,
    },
    data () {
      return {
        view: 'Step1',
        activeTab: 'second',
        accessoryStep: 0,
        accessoryList: []
      }
    },
    computed: {
      totalPrice () {
        const allAccessories = this.accessoryList.map(it => it.price * it.quantity)
        const total = allAccessories.reduce((acc, current) => acc + current)
        return Number(total.toFixed(2))
      },
      viewProps () {
        return this.accessoryStep === 0 ? {} :
          this.accessoryStep === 1 ? {
              imageUrl: '/image/headphones-black.png'
            } :
          this.accessoryStep === 2 ? {} :
          this.accessoryStep === 3 ? {} :
          {}
      }
    },
    methods: {
      goto (url) {
        window.location.href = url;
      },
      addAccessory(accessory) {
        this.accessoryStep++
        this.accessoryList.push(accessory)
      },
      deleteAccessory (indexForDeleting) {
        if (this.accessoryList.length === 1) {
          this.goto('/thankyou')
        }
        this.accessoryList.splice(indexForDeleting, 1)
      }
    },
    watch: {
      accessoryStep (val) {
        if (val === 4) {
          const node = document.querySelector('.upsells-component__content')
          fade('out', 250, node, true)
            .then(() => {
              this.activeTab = 'third'

              setTimeout(() => fade('in', 250, node, true))
            })

        }

        this.view =
          val === 0 ? 'Step1' :
          val === 1 ? 'StepWithOneItem' :
          val === 2 ? 'Step3' :
          val === 3 ? 'StepWithOneItem' :
          'StepWithOneItem'
      }
    }
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
            font-size: 18px;
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
