import '../resourses/polyfills';

require('../bootstrap');

import queryToComponent from '../mixins/queryToComponent';
import upsellsMixin from '../mixins/upsells';
import globals from '../mixins/globals';
import { getOrderAmount } from '../services/order';
import ThankYouItem from '../components/common/ThankYouItem';
import wait from '../utils/wait';
import { t } from '../utils/i18n';

const thankYou = new Vue({
    el: '#thank-you',

    components: {
        ThankYouItem,
    },

    mixins: [
        globals,
        queryToComponent,
        upsellsMixin,
    ],

    data: () => ({
        subOrder: JSON.parse(localStorage.getItem('subOrder')),
        selectedProductData: JSON.parse(localStorage.getItem('selectedProductData')) || {},
        productData: upsells.product,
        socialType: 'facebook',
        orderCustomer: upsells.orderCustomer,
        total: 0,
        sharingLinkList: {
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${window.location.href}&quote=`,
            twitter: `https://twitter.com/share?&url=${window.location.href}text=`

        }
    }),

    beforeCreate() {
      wait(
        () => document.readyState === 'complete',
        () => setTimeout(() => window.location = '#', 500),
      );
    },

    methods: {
        onClickSocialNetwork (type) {
            this.socialType = type;
            document.querySelectorAll('#social-media-tabs li').forEach(item => item.classList.remove('active'));
            document.querySelector('#' + type).classList.add('active');
        },

        share() {
            const quote = document.querySelector('#quote').value;
            window.open(this.sharingLinkList[this.socialType] + quote, null, "width=450,height=450");
        },

        getTotal() {
            getOrderAmount(new URL(document.location.href).searchParams.get('order'))
            .then(({ total_text }) => {
                this.total = total_text;
            });
        },

        getEntityPrice(index) {
            return upsells
            && upsells.orderCustomer
            && upsells.orderCustomer.productsText
            && upsells.orderCustomer.productsText[index]
            && upsells.orderCustomer.productsText[index].price_text;
        },

        saveOrderNumber() {
            localStorage.setItem('order_number', upsells.orderCustomer.number)
        }
    },

    computed: {
        orderText: () => t('thankyou.order'),
        thankYouText: () => t('thankyou.thankyou'),
        orderConfirmedText: () => t('thankyou.order.confirmed'),
        orderAcceptedText: () => t('thankyou.order.accepted'),
        orderDetailsText: () => t('thankyou.order.details'),
        warrantyText: () => t('thankyou.order.warranty'),
        subtotalText: () => t('thankyou.subtotal'),
        paymentMethodText: () => t('thankyou.payment.method'),
        orderTotalText: () => t('thankyou.order.total'),
        customerInfoText: () => t('thankyou.customer.info'),
        shippingAddressText: () => t('thankyou.shipping.address'),
        shareYourOrderText: () => t('thankyou.share'),
        weHopeText: () => t('thankyou.hope'),
        textareaBaseText: () => t('thankyou.bought'),
        shareText: () => t('thankyou.order.share.item'),


        getShippingAddress() {
            const {
                shipping_city,
                shipping_street,
                shipping_state,
                shipping_country,
                shipping_zip,
            } = upsells.orderCustomer;

            const data = {
                shipping_city,
                shipping_street,
                shipping_state,
                shipping_country,
                shipping_zip,
            }

            const queryString = Object.values(data).reduce((acc, string) => {
                return acc += string ? ` ${string}` : '';
            }, '')

            return queryString;
        },
    },

    mounted() {
        document.body.classList.remove('js-hidden');

        this.getTotal();
        this.saveOrderNumber();
    }
})
