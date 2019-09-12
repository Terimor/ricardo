<template>
    <div class="thank-you__order">
        <div class="d-flex">
            <div class="thank-you__order__image">
                <img
                    :src="order.imageUrl"
                    alt=""
                >
                <div class="quantity">{{ order.quantity }}</div>
            </div>
            <div class="thank-you__order__name">{{ order.name }}</div>
        </div>
        <div class="thank-you__order__price">{{ price }}</div>
    </div>
</template>

<script>
    import { fade } from '../../utils/common';
    import upsellsMixin from '../../mixins/upsells';

    export default {
        name: 'UpsellsItem',
        mixins: [upsellsMixin],
        props: [
            'order',
        ],

        data: () => ({
            upsellPrices: null,
        }),

        computed: {
            price() {
                if (this.upsellPrices && this.order) {
                    return this.upsellPrices[this.order.quantity].value_text
                } else {
                    return 0
                }
            }
        },

        mounted() {
            if (this.order) {
                this.getUppSells(this.order.id, this.order.quantity).then(({ data }) => {
                    this.upsellPrices = data.upsell.upsellPrices
                });
            }
        },
    };
</script>

<style lang="scss">
    .upsells-item {
        display: flex;

        &__image {
            max-width: 170px;
            padding: 7px;

            img {
                width: 100%;
                height: auto;
            }
        }

        &__content {
            flex-grow: 1;

            .benefit-list {
                list-style-type: none;
                padding: 0;

                li {
                    margin-bottom: 5px;
                }
            }
        }

        &__remove-block {
            display: flex;
            align-items: flex-end;

            & > button {
                padding: 8px;
            }
        }

        &.main {
            h5 {
                font-weight: 400;
                font-size: 25px;
                margin-top: 10px;
            }
        }

        &.is-accessory {
            .upsells-item__content {
                h5  {
                    margin-top: 8px;
                }

                .benefit-list {
                    li {
                        &:first-child {
                            font-size: 19px;
                        }
                    }
                }
            }
        }
    }
</style>
