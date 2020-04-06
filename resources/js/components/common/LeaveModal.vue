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
if (location.hostname !== 'odin.saga-be.host') {
    return;
}
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
                        <div class="leave-modal" onclick="closeLeaveModal()">
                            <div class="inside" onclick="event.stopPropagation()">
                                <div class="line1">${ this.$t('exit_popup.line1') }</div>
                                <div class="line2">${ this.$t('exit_popup.line2') }</div>
                                <div class="line3">${ this.$t('exit_popup.line3') }</div>
                                <div class="line4">${ this.$t('exit_popup.line4', { product: js_data.product.product_name }) }</div>
                                <div class="button" onclick="agreeLeaveModal()">${ this.$t('exit_popup.button') }</div>
                                <div class="link" onclick="closeLeaveModal()">${ this.$t('exit_popup.link') }</div>
                            </div>
                        </div>
                    `,
                    cookieExp: 0,
                    delay: 5,
                }),
            );
        }
    }
</script>

<style lang="scss">
div#bio_ep {
    background-color: transparent;
    bottom: 0;
    box-shadow: none;
    height: 100%;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    transform: none!important;
    width: 100%;

    #bio_ep_close {
        display: none;
    }
}

.leave-modal {
    align-items: center;
    bottom: 0;
    display: flex;
    justify-content: center;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;

    > .inside {
        background-color: #ff0;
        border: 3px solid red;
        color: #212529;
        display: flex;
        flex-direction: column;
        font-family: Arial,'Noto Sans',sans-serif;
        font-size: 1rem;
        font-weight: 400;
        justify-content: space-between;
        line-height: 1.5;
        margin: 0 10px;
        max-width: 500px;
        padding: 1rem;
        text-align: center;
        width: 100%;

        @media (min-width: 1024px) {
            max-width: 800px;
            padding: 1.5rem;
        }
    }

    .line1 {
        color: red;
        font-size: 50px;
        font-weight: 700;
        line-height: normal;
        text-transform: uppercase;

        @media only screen and (min-width: 1024px) {
            font-size: 58px;
        }
    }

    .line2 {
        color: red;
        font-size: 20px;
        font-weight: 700;
        line-height: normal;
        margin-bottom: 1rem;

        @media only screen and (min-width: 1024px) {
            font-size: 35px;
        }
    }

    .line3 {
        margin-bottom: 1rem;
    }

    .line4 {
        font-size: 20px;
        font-weight: 700;
        line-height: normal;
        margin-bottom: 1rem;

        @media only screen and (min-width: 1024px) {
            font-size: 26px;
        }
    }

    .button {
        background-color: #0ca600;
        border-color: #0ca600;
        border-radius: .3rem;
        color: #fff;
        cursor: pointer;
        font-size: 20px;
        line-height: 1.5;
        padding: .5rem 1rem;
        transition: all .15s ease-in-out;
        width: 100%;

        &:hover {
            background-color: #098000;
            border-color: #087300;
        }

        @media only screen and (min-width: 1024px) {
            font-size: 30px;
        }
    }

    .link {
        color: #000;
        cursor: pointer;
        padding: .5rem 0;
        text-decoration: underline;
    }
}
</style>
