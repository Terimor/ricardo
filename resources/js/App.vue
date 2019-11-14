<template>
  <div>
    <notice
      :showPreloader="showPreloader"
    ></notice>
    <smc7-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-if="queryParams.tpl === 'smc7' || queryParams.tpl === 'smc7p'" />
    <vmc4-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-else-if="queryParams.tpl === 'vmc4'" />
    <emc1-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-else />
    <preloader-3
      v-if="showPreloader"
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

const searchParams = new URL(location).searchParams;
const preload = searchParams.get('preload');

export default {
  name: 'app',
  data () {
    return {
      showPreloader: preload === '{preload}' || +preload === 3,
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
      document.title = this.title + this.additionalTitle;
      window.onfocus = () => document.title = this.title + this.additionalTitle;
      window.onblur = () => document.title = this.waitTitle;
    },
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
    localStorage.removeItem('order_currency')
    this.initial();
  },
}
</script>

<style lang="scss">

</style>
