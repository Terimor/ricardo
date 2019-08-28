<template>
  <div>
      <smc7-component
          :showPreloader="showPreloader"
          v-if="queryParams.tpl === 'smc7'" />
      <vmc4-component
          :showPreloader="showPreloader"
          :data="checkoutData"
          v-if="queryParams.tpl === 'vmc4'" />
      <emc1-component
          :showPreloader="showPreloader"
          :skusList="skusList"
          v-else />
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
import smc7 from './components/smc7'
import vmc4 from './components/vmc4'
import queryToComponent from './mixins/queryToComponent'

export default {
  name: 'app',
  data () {
    return {
      showPreloader: true,
      title: checkoutData.product.skus[0].name,
      additionalTitle: " Checkout",
      waitTitle: 'WAIT! YOU FORGOT: You have active cart items!'
    }
  },
  mixins: [queryToComponent],
  components: {
    emc1,
    smc7,
    vmc4
  },
  methods: {
    initial () {
      for (let key in this.queryParams) {
        window[key] = this.queryParams[key]
      }

      document.title = this.title + this.additionalTitle;

      window.onfocus = () => document.title = this.title + this.additionalTitle;
      window.onblur = () => document.title = this.waitTitle;

      this.directLinking()
    },
    directLinking () {
      const { offer_id, aff_id, direct, txid } = this.queryParams

      if (offer_id > 0 && aff_id > 0 && +direct === 1 && (txid === 'transaction_id' || txid === '{transaction_id}')) {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none'
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
    },
    skusList() {
      return checkoutData.product.skus
    }
  },
  mounted () {
    this.initial()
  }
}
</script>

<style lang="scss">

</style>
