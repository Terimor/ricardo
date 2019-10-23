<template>
  <div></div>
</template>

<script>
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
    users: recentlyBoughtNames,
    cities: recentlyBoughtCities,
    usersActive: getRandomInt(33, 44),
    bestsellerText: 'In high demand - This product is our bestseller right now...',
  }),

  props: [
    'showPreloader',
  ],

  mounted() {
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
                  if(targetButton) {
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
