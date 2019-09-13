require('../bootstrap');

import queryToComponent from '../mixins/queryToComponent';
import upsellsMixin from '../mixins/upsells';
import { groupBy } from '../utils/groupBy';
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
        socialType: 'facebook',
        total: 0,
        sharingLinkList: {
            facebook: 'http://www.facebook.com/sharer/sharer.php?u=https://landingpage.thor-be.host/checkout&quote=',
            twitter: 'https://twitter.com/home?status=https://landingpage.thor-be.host/checkout%20'
        }
    }),

    methods: {
        onClickSocialNetwork (type) {
            this.socialType = type
            document.querySelectorAll('#social-media-tabs li').forEach(item => item.classList.remove('active'))
            document.querySelector('#' + type).classList.add('active')
        },

        share() {
            const quote = document.querySelector('#quote').value
            window.open(this.sharingLinkList[this.socialType] + quote, null, "width=450,height=450")
        },

        getTotal() {
            const orders = JSON.parse(JSON.stringify(this.subOrder));
            const currentOrder = {
                id: this.queryParams.order,
                price: this.selectedProductData.prices.value,
                quantity: this.selectedProductData.quantity,
            }
            orders.push(currentOrder);

            this.addToCart(1)
            // this.getTotalPrice(groupBy(orders, 'id', 'quantity'), Math.ceil(parseFloat(this.totalPrice)));
            this.getTotalPrice(groupBy(orders, 'id', 'quantity'), {
                upsells_total: this.totalPrice,
                product_total: this.selectedProductData.prices.value,
                product_quantity: this.selectedProductData.quantity,
            });
        }
    },

    computed: {
        totalPrice() {
            console.log(this.subOrder);
            const subOrdersTotal = this.subOrder.reduce((acc, item) => {
                return acc += item.price * item.quantity;
            }, 0);

            return subOrdersTotal;
        },

        getShippingAddress() {
            const {
                shipping_street,
                shipping_city,
                shipping_state,
                shipping_zip,
                shipping_country,
            } = upsells.orderCustomer;

            return `${shipping_street}${shipping_city}${shipping_state}${shipping_zip}${shipping_country}`;
        },
    },

    mounted() {
        this.getTotal();
    }
})
