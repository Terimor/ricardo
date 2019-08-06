<template>
  <div v-if="$v">
    <div class="container offer">
      <p><span class="bold">Special Offer:</span> EchoBeat - Wireless 3D Sound</p>
      <p>Price:&nbsp;<span id="old-price" class="price-object productprice-old-object strike">₴3,598</span>
        <span class="price-span">
          <b><span id="new-price" class="price-object productprice-object"> ₴1,799</span></b>
        </span>&nbsp;(50% discount per unit)
      </p>
    </div>

    <div class="container main">
      <div class="row">
        <div class="col-md-7">
          <div class="paper main__deal">
            <div class="d-flex">
              <div class="main__sale">
                <div class="sale-badge dynamic-sale-badge ">
                  <div class="dynamic-sale-badge__background"></div>
                  <div class="dynamic-sale-badge__container">
                    <span class="badge-discount-percentage">50%</span> Off
                  </div>
                </div>
              </div>
              <p class="main__deal__text">
                <strong>Free Shipping</strong> on all orders <strong>Today!</strong>
                Do not browse away from this page! <strong>Free delivery available today</strong>
              </p>
            </div>
            <h2>Step 1: Choose your Deal</h2>
              <select-field
                  v-if="withInstallments"
                  label="Please Select Amount Of Installments"
                  popperClass="emc1-popover-variant"
                  :list="installmentsList"
                  v-model="form.installments"/>

            <h3>Article</h3>

            <span class="error" v-show="$v.form.deal.$dirty && $v.form.deal.$invalid">Please select a product promotion.</span>

            <radio-button-group
              v-model="form.deal"
              :list="dealList"
            />

            <h2>Step 2: Please select your variant</h2>

            <select-field
              popperClass="emc1-popover-variant"
              v-model="form.variant"
              :config="{
                prefix: 'EchoBeat7'
              }"
              :rest="{
                placeholder: 'Variant'
              }"
              :list="variantList" />
            <transition name="el-zoom-in-top">
              <button v-show="warrantyPrice" id="warranty-field-button">
                <label for="warranty-field" class="label-container-checkbox">
                  3 Years Additional Warranty On Your Purchase & Accessories: {{quantityOfInstallments}} ₴{{warrantyPrice}}
                  <input id="warranty-field" type="checkbox">
                  <span class="checkmark"></span>
                </label>
                <img src="/images/best-saller.png" alt="">
                <i class="fa fa-arrow-right slide-right"></i>
              </button>
            </transition>
          </div>
        </div>
        <div class="paper col-md-5 main__payment">
          <img :src="`/images/headphones-${form.variant}.png`" alt="">
          <h2>Step 3: Payment Method</h2>
          <h3>Pay Securely With: (No Fees)</h3>
          <radio-button-group
            class="main__credit-card-switcher"
            v-model="form.paymentType"
            :list="mockData.creditCardRadioList"
          />
          <transition name="el-zoom-in-top">
            <payment-form
              v-if="form.paymentType"
              :stateList="stateList"
              @showCart="isOpenSpecialOfferModal = true"
              :$v="$v"
              :installments="form.installments"
              :paymentForm="form"
              :countryCode="checkoutData.countryCode"
              :isBrazil="checkoutData.countryCode === 'BR'"
              :countryList="mockData.countryList"
              @setPromotionalModal="setPromotionalModal"
              @setAddress="setAddress"/>
          </transition>
          <div class="main__bottom">
            <img src="/images/safe_payment_en.png" alt="safe payment">
            <p><i class="fa fa-lock"></i>Safe 256-Bit SSL encryption.</p>
            <p>Your credit card will be invoiced as: "MDL*EchoBeat"</p>
          </div>
        </div>
      </div>
    </div>

    <el-dialog
      @click="isOpenPromotionModal = false"
      class="cvv-popup"
      title="Please select a product promotion."
      :lock-scroll="false"
      :visible.sync="isOpenPromotionModal">
        <div class="cvv-popup__content">
          <p class="error-container">
              Please select a product promotion.
          </p>

            <button
                @click="isOpenPromotionModal = false"
                style="height: 67px; margin: 0"
                type="button"
                class="green-button-animated">
                <span class="purchase-button-text">OK, I understand</span>
            </button>
        </div>
    </el-dialog>

    <el-dialog
      @click="isOpenSpecialOfferModal = false"
      class="common-popup special-offer-popup"
      title="SPECIAL OFFER!"
      :visible.sync="isOpenSpecialOfferModal">
        <div class="common-popup__content accessories-modal">
          <p>WAIT! 70% Of Users who bought the same product as you, also bought...</p>
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
                  <span class="purchase-button-text">YES, SEND ME MY PURCHASE WITH FREE SHIPPING NOW</span>
              </button>
              <button
                  v-if="isEmptyCart"
                  @click="isOpenSpecialOfferModal = false"
                  class="thanks">No, thanks...</button>
          </div>
        </div>
      </el-dialog>
  </div>
</template>

<script>
import emc1Validation from '../validation/emc1-validation'
import printf from 'printf'
import notification from '../mixins/notification'
import queryToComponent from '../mixins/queryToComponent'
import { getCountOfInstallments, getNotice, getRadioHtml } from '../utils/emc1';
import { stateList } from '../resourses/state';
import ProductItem from './common/ProductItem';
import Cart from './common/Cart';
import fieldsByCountry from '../resourses/fieldsByCountry';

const preparePartByInstallments = (value, installment) => Number((value / installment).toFixed(2))

export default {
  name: 'emc1',
  mixins: [notification, queryToComponent],
  components: {
    ProductItem,
    Cart,
  },
  props: ['showPreloader'],
  data () {
    return {
      mockData: {
        productList: [
          {
            key: 0,
            name: 'Echo Beat - Wireless 3D Sound white',
            title: '+1 Echo Beat - Wireless 3D Sound - 50% discount per unit',
            imageUrl: '/images/headphones-white.png',
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
            imageUrl: '/images/headphones-gold.png',
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
            imageUrl: '/images/headphones-red.png',
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
        countryList: [
          {
            value: 'US',
            text: 'USA',
            label: 'USA',
          }, {
            value: 'RU',
            text: 'Russia',
            label: 'Russia',
          }, {
            value: 'UA',
            text: 'Ukraine',
            label: 'Ukraine',
          }, {
            value: 'PT',
            text: 'Portugal',
            label: 'Portugal',
          }, {
            value: 'BR',
            text: 'Brazil',
            label: 'Brazil',
          }
        ],
        creditCardRadioList: [
          {
            label: 'Credit cards',
            value: 'credit-card',
            class: 'green-button-animated'
          }, {
            label: 'Bank payments',
            value: 'bank-payment',
            class: 'bank-payment'
          }
        ],
      },
      cart: {},
      purchase: [],
      variantList: [
        {
          label: 'EchoBeat7 White',
          text: `
              <div><img src="/images/headphones-white.png" alt=""><span>EchoBeat7 White</span></div>
            `,
          value: 'white'
        }, {
          label: 'EchoBeat7 Black',
          text: `
              <div><img src="/images/headphones-black.png" alt=""><span>EchoBeat7 Black</span></div>
            `,
          value: 'black'
        }, {
          label: 'EchoBeat7 Gold',
          text: `
              <div><img src="/images/headphones-gold.png" alt=""><span>EchoBeat7 Gold</span></div>
            `,
          value: 'gold'
        }, {
          label: 'EchoBeat7 Red',
          text: `
              <div><img src="/images/headphones-red.png" alt=""><span>EchoBeat7 Red</span></div>
            `,
          value: 'red'
        }, {
          label: 'EchoBeat7 Pink',
          text: `
              <div><img src="/images/headphones-pink.png" alt=""><span>EchoBeat7 Pink</span></div>
            `,
          value: 'pink'
        }
      ],
      stateList: (stateList[checkoutData.countryCode] || []).map((it) => ({
        value: it,
        text: it,
        label: it,
      })),
      installmentsList: [
        {
          label: 'Pay full amount now',
          text: 'Pay full amount now',
          value: 1,
        }, {
          label: 'Pay in 3 installments',
          text: 'Pay in 3 installments',
          value: 3,
        }, {
          label: 'Pay in 6 installments',
          text: 'Pay in 6 installments',
          value: 6,
        }
      ],
      form: {
        countryCodePhoneField: checkoutData.countryCode,
        deal: null,
        variant: 'white',
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
      isOpenSpecialOfferModal: true,
    }
  },
  computed: {
    isEmptyCart () {
      return Object.values(this.cart).every(it => it === 0)
    },
    withInstallments () {
      return this.checkoutData.countryCode === 'BR' || this.checkoutData.countryCode === 'MX'
    },
    quantityOfInstallments () {
      const { installments } = this.form
      return installments && installments !== 1 ? installments + 'X ' : ''
    },
    dealList () {
      return this.purchase.map((it, idx) => ({
        value: idx + 1,
        label: getRadioHtml({
          ...it,
          installments: this.form.installments,
          text: printf(it.text, { color: this.form.variant })
        })
      }))
    },
    checkoutData () {
      return checkoutData
    },
    warrantyPrice () {
      const currentDeal = this.purchase[this.form.deal - 1]

      return currentDeal && Math.round((currentDeal.newPrice || currentDeal.price) * 10) / 100
    }
  },
  watch: {
    'form.installments' (val) {
        this.setPurchase({
          variant: this.form.variant,
          installments: val,
        })
    },
    'form.variant' (val) {
      this.setPurchase({
        variant: val,
        installments: this.form.installments,
      })
    },
  },
  validations: emc1Validation,
  methods: {
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
        document.querySelector('#old-price').innerHTML = getCountOfInstallments(installments) + ' $'+ preparePartByInstallments(3598, installments).toLocaleString()
        document.querySelector('#new-price').innerHTML = getCountOfInstallments(installments) + ' $'+ preparePartByInstallments(1799, installments).toLocaleString()

        this.purchase = [
          {
            discountName: null,
            newPrice: null,
            withDiscount: false,
            text: `1x EchoBeat7 ${variant}`,
            price: preparePartByInstallments(1799, installments),
            discountText: '(50% Discount)',
            totalQuantity: 1
          }, {
            discountName: 'BESTSELLER',
            newPrice: preparePartByInstallments(3399, installments),
            withDiscount: true,
            text: `2x EchoBeat7 ${variant} + 1 FREE`,
            price: preparePartByInstallments(10794, installments),
            discountText: `(69% Discount, ${getCountOfInstallments(installments)}₴${preparePartByInstallments(1133, installments).toLocaleString()}/Unit)`,
            totalQuantity: 3
          }, {
            discountName: 'BEST DEAL',
            newPrice: preparePartByInstallments(4999, installments),
            withDiscount: true,
            text: `3x EchoBeat7 ${variant} + 2 FREE`,
            price: preparePartByInstallments(17990, installments),
            discountText: `(73% Discount, ${getCountOfInstallments(installments)}₴${preparePartByInstallments(999.80, installments).toLocaleString()}/Unit)`,
            totalQuantity: 5
          }
        ]
    },
    showNotice () {
      const notice = getNotice('EchoBeat7')
      const getNoticeHtml = () => notice.next().value

      setTimeout(() => {
        setInterval(() => {
          this.showNotification({
            content: getNoticeHtml(),
            position: document.body.offsetWidth < 768 ? 'top-left' : 'bottom-left'
          })
        }, 6000)
      }, 9000)
    }
  },
  mounted () {
    this.setCart(this.mockData.productList.reduce((acc, { key }) => {
      acc[key] = 0

      return acc
    }, {}))


    this.setPurchase({
      variant: this.form.variant,
      installments: this.form.installments,
    })

    if (this.withInstallments) {
      this.form.installments =
        this.checkoutData.countryCode === 'BR' ? 3 :
        this.checkoutData.countryCode === 'MX' ? 1 :
        1
    }

    if (+this.queryParams.preload === 3) {
      const interval = setInterval(() => {
        if (!this.showPreloader) {
          this.showNotice()
          clearInterval(interval)
        }
      }, 44)
    } else {
      this.showNotice()
    }
    const qtyIndex = this.purchase.findIndex(({ totalQuantity }) => totalQuantity === +this.queryParams.qty)
    this.form.deal = qtyIndex !== -1 ? qtyIndex + 1 : null
  }
}
</script>

<style lang="scss">
  @import "../../sass/variables";
  $white: #fff;
  $color_flush_mahogany_approx: #c0392b;
  $red: rgba(192, 57, 43, 1);
  $color_niagara_approx: #16a085;

  .accessories-modal {
    & > p {
      text-align: center;
    }

      &__bottom {
          display: flex;
          flex-direction: column;

          button {
              margin: 10px auto;
              width: 70%;
              padding: 5px;
          }

          .thanks {
              margin: 0 auto;
              border: 0;
              background-color: transparent;
              cursor: pointer;
              text-decoration: underline;
          }
      }
  }

  .container {
    max-width: 970px;
  }

  .offer {
    padding-top: 10px;
    display: flex;
    align-items: center;
    flex-direction: column;
    text-align: center;

      p {
      margin: 0;
      font-size: 15px;
      max-width: 440px;
      width: 100%;
      text-transform: uppercase;
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
      }

      .label-container-radio__discount {
        color: $color_niagara_approx;
      }

      .label-container-radio.with-discount .label-container-radio__name-price > span:nth-child(2) {
        text-decoration: line-through;
      }
    }

    &__deal {
      h3 {
        padding-left: 20px;
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

            &:hover {
                background-color: #e6e6e6;
                background-image: linear-gradient(to bottom,#e6e6e6 0,#ddd 53%,#d3d3d3 100%);
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
