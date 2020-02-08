import '../resourses/polyfills';
import queryToComponent from '../mixins/queryToComponent';
import upsellsMixin from '../mixins/upsells';
import globals from '../mixins/globals';
import { getOrderAmount } from '../services/order';
import ThankYouItem from '../components/common/ThankYouItem';
import wait from '../utils/wait';
import { t } from '../utils/i18n';


js_deps.wait(['vue', 'element'], () => {
  require('../bootstrap');

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

    data() {
        let subOrder = [];
        let selectedProductData = {};

        try {
            subOrder = JSON.parse(localStorage.getItem('subOrder')) || [];
            selectedProductData = JSON.parse(localStorage.getItem('selectedProductData')) || {};
        }
        catch (err) {

        }

        const share_params =
            [
                js_query_params.cop_id ? 'cop_id=' + encodeURIComponent(js_query_params.cop_id) : null,
                js_query_params.product ? 'product=' + encodeURIComponent(js_query_params.product) : null,
            ]
            .filter(param => !!param)
            .join('&');

        const share_url = location.origin + '/splash' + (share_params.length > 0 ? '?' + share_params : '');

        return {
            subOrder,
            selectedProductData,
            productData: js_data.product,
            socialType: 'facebook',
            orderCustomer: js_data.order_customer,
            total: 0,
            sharingLinkList: {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${share_url}&quote=`,
                twitter: `https://twitter.com/share?&url=${share_url}&text=`

            }
        };
    },

    beforeCreate() {
      wait(
        () => document.readyState === 'complete',
        () => setTimeout(() => window.location = '#', 500),
      );
    },

    methods: {
        onClickSocialNetwork (type) {
            if (type) {
                this.socialType = type;
                [].forEach.call(document.querySelectorAll('#social-media-tabs li'), item => item.classList.remove('active'));
                document.querySelector('#' + type).classList.add('active');
            }
        },

        share() {
            const quote = document.querySelector('#quote').value;
            window.open(this.sharingLinkList[this.socialType] + quote, null, "width=450,height=450");
        },

        getTotal() {
            getOrderAmount(js_query_params.order)
                .then(res => {
                    this.total = res ? res.total_text : 0;
                });
        },

        getEntityPrice(index) {
          return js_data.order_customer
            && js_data.order_customer.productsText
            && js_data.order_customer.productsText[index]
            && js_data.order_customer.productsText[index].price_text;
        },

        saveOrderNumber() {
            localStorage.setItem('order_number', js_data.order_customer && js_data.order_customer.number || '');
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
            const data = {
                shipping_city: js_data.order_customer && js_data.order_customer.shipping_city || '',
                shipping_street: js_data.order_customer && js_data.order_customer.shipping_street || '',
                shipping_state: js_data.order_customer && js_data.order_customer.shipping_state || '',
                shipping_country: js_data.order_customer && js_data.order_customer.shipping_country || '',
                shipping_zip: js_data.order_customer && js_data.order_customer.shipping_zip || '',
            }

            const queryString = Object.values(data).reduce((acc, string) => {
                return acc += string ? ` ${string}` : '';
            }, '')

            return queryString;
        },
    },

    mounted() {
        this.getTotal();
        this.saveOrderNumber();
    }
  });

});
