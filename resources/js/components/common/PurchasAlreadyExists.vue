<template>
  <div class="alert alert-success purchase-exists-alert" v-html="textAlert"></div>
</template>

<script>
import { t } from '../../utils/i18n';

export default {
  name: 'PurchasAlreadyExists',

  computed: {
    textAlert: () => t('checkout.purchase_exists', {
      order: localStorage.getItem('order_number'),
      email: 'support@maxdeals.ltd',
    }),
  },
}
</script>

<style lang="scss">
.purchase-exists-alert {
  margin-top: 15px;
}
</style>
