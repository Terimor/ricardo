<template>

  <transition
    name="el-zoom-in-top">

    <button
      v-show="warrantyPriceText"
      id="warranty-field-button">

      <label for="warranty-field" class="label-container-checkbox">
        <span v-html="textWarranty"></span>: {{quantityOfInstallments}} {{warrantyPriceText}}
        <input id="warranty-field" type="checkbox" v-model="form.isWarrantyChecked">
        <span class="checkmark"></span>
      </label>

      <img :src="$root.cdn_url + '/assets/images/best-saller.png'" alt="">

      <i class="fa fa-arrow-left slide-left"></i>
      <i class="fa fa-arrow-right slide-right"></i>

    </button>

  </transition>
  
</template>


<script>
  
  export default {

    name: 'Warranty',


    props: [
      'form',
    ],


    computed: {

      quantityOfInstallments() {
        return this.form.installments !== 1
          ? this.form.installments + '× '
          : '';
      },

      warrantyPriceText() {
        const prices = checkoutData.product.prices;

        if (!this.form.deal) {
          return 0;
        }

        switch (this.form.installments) {
          case 1:
            return prices[this.form.deal].warranty_price_text;
          case 3:
            return prices[this.form.deal].installments3_warranty_price_text;
          case 6:
            return prices[this.form.deal].installments6_warranty_price_text;
        }

        return 0;
      },

      textWarranty() {
        return this.$t('checkout.warranty');
      },

    },

  };

</script>


<style lang="scss" scoped>

  #warranty-field-button {
    display: flex;
    justify-content: center;
    flex-direction: column;
    width: 100%;
    position: relative;
    padding: 0;
    background-color: rgba(216, 216, 216, .71);
    border-radius: 5px;
    border: 1px solid rgba(0, 0, 0, 0.4);
    outline: none;

    &:hover {
      background-color: rgba(191,191,191,.71);
      background-image: linear-gradient(to bottom, #e6e6e6 0, #ccc 100%);
    }
  }

  label[for=warranty-field] {
    font-weight: bold;
    line-height: 1.8;
    text-align: left;
    margin: 17px 70px 18px 100px;
    padding: 0;
    text-transform: capitalize;
    font-size: 16px;

    [dir="rtl"] & {
      margin: 17px 100px 18px 70px;
      text-align: right;
    }

    .small & {
      font-size: 14px;
      margin: 18px 20px 18px 75px;

      [dir="rtl"] & {
        margin: 18px 75px 18px 20px;
      }
    }

    @media screen and (max-width: 767px) {
      & {
        font-size: 14px;
        margin: 18px 40px 18px 80px;

        [dir="rtl"] & {
          margin: 18px 80px 18px 40px;
        }
      }

      .small & {
        margin: 18px 40px 18px 80px;
        font-size: 14px;

        [dir="rtl"] & {
          margin: 18px 80px 18px 40px;
        }
      }
    }
  }

  input[type=checkbox] {
    position: absolute;
    top: 23px;
    left: 45px;
  }

  .checkmark {
    top: 3px;
    left: -30px;

    [dir="rtl"] & {
      left: auto;
      right: -30px;
    }

    .small & {
      top: 2px;
    }

    @media screen and (max-width: 767px) {
      & {
        top: 2px;
      }
    }
  }

  img {
    position: absolute;
    width: 30px;
    height: auto;
    top: -7px;
    right: -7px;

    [dir="rtl"] & {
      left: -7px;
      right: auto;
      transform: rotate(-24deg);
    }
  }

  .fa-arrow-left {
    display: none;
    position: absolute;
    font-size: 18px;
    color: #dc003a;
    top: 20px;
    right: 10px;
    

    [dir="rtl"] & {
      display: block;
    }
  }

  .fa-arrow-right {
    position: absolute;
    font-size: 18px;
    color: #dc003a;
    top: 20px;
    left: 10px;

    [dir="rtl"] & {
      display: none;
    }
  }
  
</style>
