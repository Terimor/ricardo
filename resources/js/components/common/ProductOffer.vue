<template>

    <div class="offer">
        <p><span class="bold">{{ speсialOffer }}: </span> {{product.long_name}}</p>
        <p>{{ price }}:&nbsp;
            <span id="old-price" class="price-object productprice-old-object strike">
                {{ product.prices['1']['old_value_text']}}
            </span>
            <span class="price-span">
                <b><span id="new-price" class="price-object productprice-object">
                    {{ product.prices['1']['value_text'] }}
                </span></b>
            </span>&nbsp;
            ({{ product.prices['1'].discount_percent }}% {{ discount }})
        </p>
    </div>

</template>

<script>
    import { t } from '../../utils/i18n';

    export default {
        name: 'ProductOffer',
        computed: {
            product() {
                return js_data.product;
            },
            speсialOffer: () => t('checkout.header_banner.prefix'),
            price: () => t('checkout.header_banner.price'),
            discount: () => t('checkout.header_banner.discount'),
        },

    };
</script>

<style lang="scss">
    .offer {
        text-align: center;
    }
</style>
