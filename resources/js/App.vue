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
    <fmc5
      :showPreloader="showPreloader"
      v-else-if="queryParams.tpl === 'fmc5'" />
    <emc1-component
      :showPreloader="showPreloader"
      :skusList="skusList"
      v-else />
    <preloader-3
      v-if="showPreloader"
      :countryCode="countryCode"
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
import fmc5 from './components/fmc5'
import queryToComponent from './mixins/queryToComponent'


export default {
  name: 'app',
  data () {
    return {
      showPreloader: js_query_params.preload === '{preload}' || +js_query_params.preload === 3,
      title: js_data.product.page_title,
      additionalTitle: ' ' + t('checkout.page_title'),
      waitTitle: t('checkout.page_title.wait'),
    }
  },
  mixins: [queryToComponent],
  components: {
    emc1,
    smc7,
    vmc4,
    fmc5,
  },
  methods: {
    initial () {
      document.title = this.title + this.additionalTitle;
      window.onfocus = () => document.title = this.title + this.additionalTitle;
      window.onblur = () => document.title = this.waitTitle;
    },
  },
  computed: {
    countryCode() {
      return js_data.country_code;
    },
    skusList() {
      return Array.isArray(js_data.product.skus)
        ? js_data.product.skus
        : [];
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
