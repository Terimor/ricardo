<template>
<div class="cart-container">
    <div
        class="cart-item"
        v-for="({ key, imageUrl, name, newPrice }) in productList"
        v-if="cart[key] !== 0">
        <div class="cart-item__image">
            <img :src="imageUrl" alt="">
        </div>
        <div class="cart-item__content">
            <span class="quantity">{{cart[key]}}</span>
            <span class="name">{{name}}</span>
            <span class="price">{{newPrice}}</span>
            <i
                @click="$emit('setCart', {
                    ...cart,
                    [key]: 0
                })"
                class="fa fa-times-circle"
                aria-hidden="true"></i>
        </div>
    </div>
</div>
</template>

<script>
  export default {
    name: 'Cart',
    props: ['cart', 'productList'],
  };
</script>

<style lang="scss">
@import "../../../sass/variables";
.cart-container {
    padding-top: 20px;

    .cart-item {
        display: flex;

        &__image {
            width: 15%;
            padding: 0 20px;

            img {
                width: 100%;
                height: auto;
            }
        }

        &__content {
            flex-grow: 1;
            display: flex;
            align-items: center;

            .quantity {
                background: #606266;
                width: 20px;
                min-width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-right: 10px;
                color: white;
            }

            .name {
                flex-grow: 1;
                padding-right: 10px;
            }

            .price {
                white-space: nowrap;
            }

            .fa-times-circle {
                margin-left: 10px;
                cursor: pointer;
                font-size: 20px;
                color: $red;
            }
        }
    }

    @media screen and ($s-down) {
        .cart-item {
            &__image {
                width: 20%;
                padding: 0 10px;
            }
        }
    }
}
</style>
