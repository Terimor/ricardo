require('../bootstrap');

import queryToComponent from '../mixins/queryToComponent';
import upsellsMixin from '../mixins/upsells';
import { getOrderAmount } from '../services/order';
import ThankYouItem from '../components/common/ThankYouItem';

const thankYou = new Vue({
    el: '#thank-you',

    components: {
        ThankYouItem,
    },

    mixins: [
        queryToComponent,
        upsellsMixin,
    ],

    data: () => ({
        subOrder: JSON.parse(localStorage.getItem('subOrder')),
        selectedProductData: JSON.parse(localStorage.getItem('selectedProductData')),
        productData: upsells.product,
        socialType: 'facebook',
        total: 0,
        sharingLinkList: {
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${window.location.href}&quote=`,
            twitter: `https://twitter.com/share?&url=${window.location.href}text=`

        }
    }),

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
                return acc += string ? string : '';
            }, '')

            return queryString;
        },
    },

    mounted() {
        this.getTotal();
        this.saveOrderNumber();
    }
})
