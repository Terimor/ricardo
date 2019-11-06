<template>
    <div
        :id="id"
        class="upsells-item"
        :class="{
            'main': !withRemoveButton,
            'is-accessory': withRemoveButton
        }">
        <div class="upsells-item__image">
            <img :src="imageUrl" alt="">
        </div>
        <div class="upsells-item__content">
            <h5>{{name}}</h5>
            <ul class="benefit-list">
                <li
                    v-for="benefit in benefitList"
                    :key="benefit"
                >
                    {{ benefit }}
                </li>
                <li>{{ textSubtotal }}:
                    {{ finalPrice || subtotal }}
                </li>
                <li v-if="warranty">
                    {{ textWarranty }}:
                    {{ warranty }}
                </li>
            </ul>
        </div>
        <div
            class="upsells-item__remove-block"
            v-if="withRemoveButton"
        >
            <el-button
                type="danger"
                @click="deleteAccessory"
            >
                {{ textRemove }}
            </el-button>
        </div>
    </div>
</template>

<script>
    import { fade } from '../../utils/common';
    import upsellsMixin from '../../mixins/upsells';

    export default {
        name: 'UpsellsItem',
        mixins: [upsellsMixin],
        props: [
            'name',
            'benefitList',
            'withRemoveButton',
            'imageUrl',
            'idx',
            'itemData',
            'price',
            'quantity',
            'subtotal',
            'finalPrice',
            'warranty',
        ],

        data: () => ({
            upsellPrices: null,
        }),

        computed: {
            id () {
                return 'upsells-item-' + this.idx
            },

            textSubtotal() {
                return this.$t('upsells.subtotal');
            },

            textWarranty() {
                return this.$t('upsells.warranty');
            },

            textRemove() {
                return this.$t('upsells.remove');
            },
        },

        methods: {
            deleteAccessory () {
                let subOrder = [];

                try {
                    subOrder = JSON.parse(localStorage.getItem('subOrder')) || [];
                    const deleteIndex = subOrder.findIndex((item) => item.id == this.itemData.id);
                    subOrder.splice(deleteIndex, 1);
                }
                catch (err) {

                }

                localStorage.setItem('subOrder', JSON.stringify(subOrder));
                this.$emit('deleteAccessory', this.idx);
            },
        }
    };
</script>

<style lang="scss">
.upsells-component {
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
}
</style>
