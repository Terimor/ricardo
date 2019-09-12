<template>
    <div :id="id" class="upsells-item" :class="{ 'main': !withRemoveButton, 'is-accessory': withRemoveButton }">
        <div class="upsells-item__image">
            <img :src="imageUrl" alt="">
        </div>
        <div class="upsells-item__content">
            <h5>{{name}}</h5>
            <ul class="benefit-list">
                <li v-for="benefit in benefitList">{{benefit}}</li>
                <li>Subtotatal: {{ subtotal || total }}</li>
            </ul>
        </div>
        <div class="upsells-item__remove-block" v-if="withRemoveButton">
            <el-button type="danger" @click="deleteAccessory">Remove</el-button>
        </div>
    </div>
</template>

<script>
  import { fade } from '../../utils/common';

  export default {
    name: 'UpsellsItem',
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
    ],

    data: () => ({
        total: 0,
    }),

    computed: {
      id () {
        return 'upsells-item-' + this.idx
      }
    },

    mounted() {
        if (this.itemData) {
            this.getTotalPrice(this.itemData, this.price * this.quantity);
        }
    },

    methods: {
      deleteAccessory () {
        const node = document.querySelector('#' + this.id)
        fade('out', 250, node)
          .then(() => {
                this.$emit('deleteAccessory', this.idx)
            })
      },

      getTotalPrice(data, total) {
        return axios
          .post(`${window.location.origin}/calculate-upsells-total`,
          {
              upsells: data,
              total: total
          },
          {
            credentials: 'same-origin',
            headers: {
              accept: 'application/json',
                'content-type': 'application/json'
            },
          })
          .then(({ data }) => {
            this.total = data.value_text;
          });
      },
    }
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
