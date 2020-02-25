<template>
  <div></div>
</template>

<script>
import { t } from '../../utils/i18n';
import notification from '../../mixins/notification';
import queryToComponent from '../../mixins/queryToComponent';
import { getNotice } from '../../utils/emc1';
import { fade, getRandomInt } from '../../utils/common';

export default {
  name: 'Notice',

  mixins: [
    notification,
    queryToComponent,
  ],

  data: () => ({
    users: js_data.recently_bought_names,
    cities: js_data.recently_bought_cities,
    usersActive: getRandomInt(33, 44),
    bestsellerText: t('checkout.notification.bestseller'),
  }),

  props: [
    'showPreloader',
  ],

  mounted() {
    if (+js_query_params.recentlybought === 0) {
      return;
    }

    if (this.showPreloader) {
      const interval = setInterval(() => {
        if (!this.showPreloader) {
          this.showNotice();
          clearInterval(interval);
        }
      }, 44)
    } else {
      this.showNotice();
    }
  },

  methods: {
    showNotice () {
      const { users, cities, usersActive, bestsellerText } = this;
      const notice = getNotice({
        users,
        cities,
        usersActive,
        bestsellerText,
        paypal: !!this.$root.paypalEnabled && js_data.country_code !== 'br',
      });

      const getNoticeHtml = () => notice.next().value;

      setTimeout(() => {
        setInterval(() => {

          this.showNotification({
            content: getNoticeHtml()
          });

          const targetNotice = document.querySelector('.recently-notice_paypal');
          const targetButton = document.querySelector('#paypal-button');

          if(targetNotice) {
              targetNotice.addEventListener('click', function () {
                  if(targetButton && targetButton.scrollIntoView) {
                      targetButton.scrollIntoView();
                  }
              });
          };
        }, 6000)
      }, 9000)
    }
  },
}
</script>
