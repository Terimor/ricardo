<template>
    <li class="list-group-item order-heading"  @click="changeActive">
        <div style="cursor: pointer">Order #{{order.number}}, Status: {{order.status}}, Date: {{order.created_at}}, Total: {{order.total_paid}} {{order.currency}}</div>
        <div class="order-info" v-if="isActive">
            <div class="row pt-4">
                <div class="col-md-6 border pt-4">
                    <h4>Order Info</h4>

                    <h5> Products </h5>
                    <div class="tab;e-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in order.products">
                                    <td>{{product.sku_code}}</td>
                                    <td>{{product.quantity}}</td>
                                    <td>{{order.currency}} {{product.quantity * product.price_usd}}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{order.currency}} {{order.total_paid}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <h5>Customer information</h5>
                    <ul>
                        <li>{{order.customer_first_name}} {{order.customer_last_name}}</li>
                        <li>Phone: {{order.customer_phone}}</li>
                        <li>Order status: {{order.status}}</li>
                    </ul>

                </div>
                <div class="col-md-6 border pt-4">
                    <h4>Shipping info</h4>
                    {{order.shipping_country}}<br>
                    {{order.shipping_zip}}<br>
                    {{order.shipping_state}}<br>
                    {{order.shipping_city}}<br>
                    {{order.shipping_street}}<br>
                    {{order.shipping_street2}}<br>
                    {{order.shipping_building}}<br>
                    {{order.shipping_apt}}<br>
                    <template v-if="order.trackings && order.trackings.length">
                        <h4>Tracking numbers</h4>
                        <ul>
                            <li v-for="tracking in order.trackings">
                                <a :href="'http://sprtdls.aftership.com/'+tracking.number" target="_blank">
                                    {{tracking.number}}-{{tracking.status}}
                                </a>
                            </li>
                        </ul>
                    </template>
                </div>
            </div>

        </div>
    </li>

</template>

<script>
  export default {
    name: "OrderDetail",
    props: ['order', 'isActive'],
    methods: {
      changeActive() {
        this.$emit('click');
      }
    }
  }
</script>

<style>

</style>
