<template>
<div class="step-3">
    <h3><span class="orange">You Can'y Leave Without Taking ADVANTAGE of this SPECIAL OFFER!</span></h3>
    <h5>Last chance! Get 1 more eazyclean for just <span class="price">$25.3!</span></h5>

    <div class="content-with-image">
        <img src="/images/acustop.jpg" alt="">
        <div class="text-container">
            <p class="right"><img src="/images/EchoBeat-logo.png" alt=""></p>
            <p class="last">This is your LAST CHANCE to take advantage of this amazing deal!</p>
            <h2>Fur-Cleaning Brush</h2>
            <p><i>Get a EazyClean for our friends and family!</i></p>
        </div>
    </div>

    <div class="upsells-component__bot">
        <select-field
            label="Please choose:"
            v-model="quantity"
            :list="[
                {
                    label: '1x EazyClean - $25',
                    text: '1x EazyClean - $25',
                    value: 1,
                }, {
                    label: '2x EazyClean - $50',
                    text: '2x EazyClean - $50',
                    value: 2,
                }, {
                    label: '3x EazyClean - $75',
                    text: '3x EazyClean - $75',
                    value: 3,
                },
            ]"
        />
        <green-button @click="addToCart(quantity)">Add To Cart</green-button>
    </div>
</div>
</template>

<script>
  import upsells from '../../mixins/upsells'
  export default {
    name: 'Step3',
    mixins: [upsells],
    props: {
      name: {
        default: 'EazyClean'
      },
      benefitList: {
        default () {
          return [
            'Lorem ipsum dolor sit amet.',
            'Lorem ipsum dolor sit amet. Hello',
            'Lorem ipsum dolor sit amet. Lorem',
            'Lorem ipsum dolor sit amet.',
          ]
        }
      },
      imageUrl: {
        default: '/images/headphones-white.png'
      },
      price: {
        default: 25
      }
    },
    data () {
      return {
        quantity: 1
      }
    }
  };
</script>

<style lang="scss">
    .step-3 {
        h3 {
            color: #d4513a;
            margin-bottom: 0;
            text-align: center;
        }

        h5 {
            text-transform: uppercase;
        }

        .price {
            color: #0d840d;
        }

        .content-with-image {
            position: relative;

            img {
                width: 100%;
                height: auto;
            }

            .text-container {
                padding: 2% 2% 15% 30%;
                position: absolute;
                top: 0;
                width: 100%;
                height: 100%;

                & > h2 {
                    font-size: 24px;
                }

                .right {
                    text-align: right;
                    padding-bottom: 10px;

                    img {
                        width: 50%;
                    }
                }
            }
        }

        .upsells-component__bot {
            flex-direction: column;
            align-items: center;

            & > .green-button-animated {
                width: 80%;
                max-width: 100%;
            }

            & > .select {
                width: 40%;
                margin-bottom: 20px;
            }
        }

        @media screen and (max-width: 768px) {
            .content-with-image {
                .last {
                    font-size: 15px;
                }

                h2 {
                    margin: 0;
                    font-size: 18px;
                }
            }

            .upsells-component__bot {
                & > .select {
                    width: 100%;
                }

                & > .green-button-animated {
                    width: 100%;
                }

                i {
                    font-size: 14px;
                }
            }
        }
    }

</style>
