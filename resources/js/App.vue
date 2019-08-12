<template>
  <div>
    <emc1
      :showPreloader="showPreloader"></emc1>
    <preloader-3
      v-if="+queryParams.preload === 3"
      @finish-preload="showPreloader = false"
      :countryCode="checkoutData.countryCode"
      :showPreloader="showPreloader"></preloader-3>
    <leave-modal
      v-if="+queryParams.exit === 1"></leave-modal>
  </div>
</template>

<script>
import emc1 from './components/emc1'
import queryToComponent from './mixins/queryToComponent'

export default {
  name: 'app',
  data () {
    return {
      showPreloader: true
    }
  },
  mixins: [queryToComponent],
  components: {
    emc1
  },
  methods: {
    initial () {
      for (let key in this.queryParams) {
        window[key] = this.queryParams[key]
      }

      const title = document.title

      window.onfocus = function () {
        document.title = title
      }
      window.onblur = function () {
        document.title = 'WAIT! YOU FORGOT: You have active cart items!'
      }

      this.directLinking()
    },
    directLinking () {
      const { offer_id, aff_id, direct, txid } = this.queryParams

      if (offer_id > 0 && aff_id > 0 && +direct === 1 && (txid === 'transaction_id' || txid === '{transaction_id}')) {
        const iframe = document.createElement('iframe');
        iframe.src = `https://lai.go2cloud.org/aff_c?offer_id=${offer_id}&aff_id=${aff_id}`

        document.body.append(iframe)
      }

      if (offer_id > 0 && aff_id > 0 && +direct === 1 && (txid !== 'transaction_id' || txid !== '{transaction_id}' || txid == null)) {
        window.txidjs = txid

        Cookies.set('txidjs', txid)
      }
    }
  },
  computed: {
    checkoutData() {
      return checkoutData
    }
  },
  mounted () {
    this.initial()
  }
}
</script>

<style lang="scss">

</style>
