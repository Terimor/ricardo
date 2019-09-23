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
    data: {
      names: ['Oliver', 'asd', 'John'],
      cities: ['NY', 'LA', 'LONDON'],
      usersActive: getRandomInt(33, 44),
      bestsellerText: 'In high demand - This product is our bestseller right now...'
    }
  }),

  props: [
    'showPreloader',
  ],

  mounted() {
    if (+this.queryParams.preload === 3) {
      const interval = setInterval(() => {
        if (!this.showPreloader) {
          this.showNotice()
          clearInterval(interval)
        }
      }, 44)
    } else {
      this.showNotice();
    }
  },

  methods: {
    showNotice () {
      const notice = getNotice({
        users: this.data.names,
        cities: this.data.cities,
        usersActive: this.data.usersActive,
        bestsellerText: this.data.bestsellerText
      });

      const getNoticeHtml = () => notice.next().value

      setTimeout(() => {
        setInterval(() => {
          this.showNotification({
            content: getNoticeHtml(),
            position: document.body.offsetWidth < 768 ? 'top-left' : 'bottom-left'
          })
        }, 1000)
      }, 1000)
    }
  },
}
</script>

<style lang="scss">

</style>
