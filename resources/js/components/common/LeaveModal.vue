<template></template>

<script>
    import wait from '../../utils/wait';

    export default {
        props: {
            showPreloader: {
                type: Boolean,
                default: false
            }
        },

        computed: {

            product() {
                return js_data.product || null;
            },

            productImage() {
                return this.product && this.product.logo_image || '';
            },

            priceIndex() {
                const indexes = Object.keys(this.product.prices);
                let bestseller = null;
                let popular = null;

                for (let index of indexes) {
                    if (this.product.prices[index].is_bestseller) {
                        bestseller = +index;
                    }
                    if (this.product.prices[index].is_popular) {
                        popular = +index;
                    }
                }

                return bestseller || popular || +indexes[0];
            },

            mainQuantity() {
                return this.priceIndex === 3
                    ? 2
                    : this.priceIndex === 5
                        ? 3
                        : this.priceIndex;
            },

            freeQuantity() {
                return this.priceIndex === 3
                    ? 1
                    : this.priceIndex === 5
                        ? 2
                        : 0;
            },

            priceText() {
                return this.product.prices[this.priceIndex].value_text;
            },

            textText() {
                return this.$t('exit_popup.text', {
                    count: this.mainQuantity,
                    amount: this.freeQuantity,
                    payment_details: this.priceText,
                });
            },

            textAgree() {
                return this.$t('exit_popup.agree');
            },

            textClose() {
                return this.$t('exit_popup.close');
            },

        },

        mounted() {
            window.closeLeaveModal = () => {
                var event = document.createEvent('Events');
                event.initEvent('click', true, false);

                document.querySelector('#bio_ep_close').dispatchEvent(event);
            }
            window.agreeLeaveModal = () => {
                window.closeLeaveModal()

                const element = document.querySelector('.main__deal');

                if (element && element.scrollIntoView) {
                    element.scrollIntoView();
                }
            };

            wait(
                () => {
                    return !!window.bioEp && !this.showPreloader;
                },
                () => bioEp.init({
                    width: 450,
                    html: `
                        <div class="leave-modal">
                            <img class="lazy" src="${this.productImage}" alt="" />
                            ${this.textText}
                            <button class="offer-btn" onclick="agreeLeaveModal()">${this.textAgree}</button>
                            <button class="close-btn" onclick="closeLeaveModal()">${this.textClose}</button>
                        </div>
                        `,
                    cookieExp: 0,
                    delay: 15,
                }),
            );
        }
    }
</script>

<style lang="scss">
div#bio_ep {
    padding: 3% 4% 5%;
    height: auto;

    & > div {
        display: flex;
        flex-direction: column;

        & > img {
            width: 80%;
            height: auto;
            margin: 0 auto 20px;
            flex-shrink: 0;
        }

        p {
            font-weight: 700;
            font-size: 1.1rem;
        }
    }
    #bio_ep_close {
        display: none;
    }
}

.leave-modal {
    p {
        line-height: 1.7rem;
        text-align: center;
    }

    .green {
        margin: 0.2rem 0;
        font-weight: 400;
        color: #0f9b0f;
    }

    .offer-btn {
        font-size: 1.2rem;
        background: linear-gradient(180deg, #9fdf23, #6ac800);
        border: 1px solid #a4a4a4;
        color: #fff;
        border-radius: 3px;
        padding: 7px 5px;
        font-weight: 700;
        margin: 15px 0 22px;
    }

    .close-btn {
        color: #a4a4a4;
        background: transparent;
        border: 0;
        text-decoration: underline;
        transform: none !important;
    }

    button {
        outline: none;
        cursor: pointer;
    }

    button:active {
        transform: translateY(1px)
    }
}
</style>
