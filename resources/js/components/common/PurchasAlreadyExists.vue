<template>
  <div class="alert">
    You already made your purchase&nbsp;&mdash; your confirmation&nbsp;#&nbsp;is&nbsp;{{ orderNumber }} -
    If&nbsp;you have any questions, you can reach&nbsp;us under help@support-deals.com
  </div>
</template>

<script>
export default {
  name: 'PurchasAlreadyExists',

  data: () => ({
    orderNumber: localStorage.getItem('odin_order_id'),
  }),
}
</script>

<style>
.alert {
  width: 100%;
  padding: 20px;
  background-color: #6bbd6e;
  color: white;
  margin-top: 15px;
}
</style>
