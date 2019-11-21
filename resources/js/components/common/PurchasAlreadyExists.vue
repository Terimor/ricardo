<template>
  <div class="alert" v-html="textAlert"></div>
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
.alert {
  width: 100%;
  padding: 20px;
  background-color: #6bbd6e;
  color: white;
  margin-top: 15px;
}
</style>
