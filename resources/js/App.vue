<template>
  <div>
    <notice
      :showPreloader="showPreloader"
    ></notice>
    <smc7-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-if="queryParams.tpl === 'smc7'" />
    <vmc4-component
      :showPreloader="showPreloader"
      :data="checkoutData"
      v-else-if="queryParams.tpl === 'vmc4'" />
    <emc1-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-else />
    <preloader-3
      v-if="+queryParams.preload === 3"
      :countryCode="checkoutData.countryCode"
      :show-preloader.sync="showPreloader"/>
    <leave-modal
      v-if="+queryParams.exit === 1"
      :show-preloader="showPreloader"
    />
  </div>
</template>

<script>
import { t } from './utils/i18n';
import emc1 from './components/emc1'
import smc7 from './components/smc7'
import vmc4 from './components/vmc4'
import queryToComponent from './mixins/queryToComponent'

export default {
  name: 'app',
  data () {
    return {
      showPreloader: true,
      title: checkoutData.product.page_title,
      additionalTitle: ' ' + t('checkout.page_title'),
      waitTitle: t('checkout.page_title.wait'),
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
        document.cookie = 'txid=' + encodeURIComponent(txid);
      }
    }
  },
  computed: {
    checkoutData() {
      return checkoutData
    },
    skusList() {
      return checkoutData.product.skus
    },
  },
  mounted () {
    if (this.queryParams['preload'] === undefined || Number(this.queryParams['preload']) !== 3) {
        this.showPreloader = false
    };

    localStorage.removeItem('order_currency')
    this.initial();
  },
  beforeCreate() {
    if (document.location.pathname.split('/').pop() === 'checkout') {
      document.body.classList.add('tpl-' + (new URL(document.location).searchParams.get('tpl') || 'emc1'));
    }
  },
}
</script>

<style lang="scss">

</style>
