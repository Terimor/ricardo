<template>
<div class="product-item">
  <div class="product-item__image">
    <img :src="imageUrl" alt="">
  </div>
  <div class="product-item__main">
    <h5>{{title}}</h5>
    <ul>
      <li v-for="advantage in advantageList">{{advantage}}</li>
    </ul>
  </div>
  <div class="product-item__bottom">
    <div class="product-item__bottom__price">
      <p class="regular">Regular price: ${{regularPrice.toLocaleString()}}</p>
      <p class="new">${{newPrice.toLocaleString()}}</p>
    </div>
    <div class="product-item__bottom__quantity-block">
      <el-input-number v-model="cart" :min="0" :max="maxQuantity"/>
    </div>
  </div>
</div>
</template>

<script>
  export default {
    name: 'ProductItem',
    props: ['imageUrl', 'title', 'advantageList', 'regularPrice', 'newPrice', 'maxQuantity', 'keyProp', 'value'],
    computed: {
      cart: {
        set (val) {
          this.$emit('setCart', {
            [this.keyProp]: val
          })
        },
        get () {
          return this.value
        }
      }
    }
  };
</script>

<style lang="scss">
@import "../../../sass/variables";
.product-item {
  display: flex;
  flex-wrap: wrap;
  color: #0a0f0a;
  margin-top: 15px;

  &__image {
    width: 30%;
    padding: 0 20px;

      img {
          width: 100%;
          height: auto;
      }
  }

  &__main {
    flex-grow: 1;

    h5 {
      margin: 0;
    }

    ul {
      margin-top: 6px;
    }
  }

  &__bottom {
    display: flex;
    width: 100%;
    padding-left: 30%;

    &__price {
      width: 50%;

      .regular {
        text-decoration: line-through;
        margin-bottom: 10px;
      }

      .new {
        font-weight: bold;
      }
    }

    &__quantity-block {
      width: 50%;

      .el-input-number {
          &__decrease, &__increase {
              bottom: 1px;
              display: flex;
              align-items: center;
              justify-content: center;
              color: white;
              background-color: #0f9b0f;
              border-radius: 10px;
          }

          input {
              border: 0;
          }
      }
    }
  }

  @media screen and ($s-down) {
      &__main {
          width: 70%;
      }

      &__bottom {
          padding-left: 0;

          &__quantity-block {
            width: unset;
          }
      }
  }
}
</style>
