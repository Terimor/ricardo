require('../bootstrap');

const thankYou = new Vue({
    el: '#thank-you',

    data: () => ({
        subOrder: JSON.parse(localStorage.getItem('subOrder')),
        selectedProductData: JSON.parse(localStorage.getItem('selectedProductData')),
        socialType: 'facebook',
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
        }
    },

    computed: {
        totalPrice() {
            const subOrdersTotal = this.subOrder.reduce((acc, item) =>{
                return acc += item.price
            }, 0)

            return subOrdersTotal + this.selectedProductData.prices.value;
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
        }
    }
})
