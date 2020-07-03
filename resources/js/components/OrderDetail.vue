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
                                    <th>Sku</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in order.products" :class="product.is_paid ? '' : 'text-secondary font-weight-light font-italic'" :title="product.is_paid ? '' : 'Not paid'">
                                    <td>{{product.name}}</td>
                                    <td>{{product.sku_code}}</td>
                                    <td>{{product.quantity}}</td>
                                    <td>{{order.currency}} {{product.quantity * product.price_usd}}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total Paid</th>
                                    <th>{{order.currency}} {{order.total_paid}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>



                </div>
                <div class="col-md-6 border pt-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Shipping address</h5>
                            <ul>
                                <li v-if="order.shipping_country">Country: {{order.shipping_country}}</li>
                                <li v-if="order.shipping_zip">ZIP: {{order.shipping_zip}}</li>
                                <li v-if="order.shipping_state">State: {{order.shipping_state}}</li>
                                <li v-if="order.shipping_city">City: {{order.shipping_city}}</li>
                                <li v-if="order.shipping_street">Street: {{order.shipping_street}}</li>
                                <li v-if="order.shipping_street2">Street/district: {{order.shipping_street2}}</li>
                                <li v-if="order.shipping_building">Building: {{order.shipping_building}}</li>
                                <li v-if="order.shipping_apt">Apartments: {{order.shipping_apt}}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Customer information</h5>
                            <ul>
                                <li>{{order.customer_first_name}} {{order.customer_last_name}}</li>
                                <li>Phone: {{order.customer_phone}}</li>
                                <li>Order status: {{order.status}}</li>
                            </ul>
                        </div>
                    </div>


                    <div class="row" v-if="order.trackings && order.trackings.length">
                        <div class="col-md-12 pt-4">
                            <h5>Tracking numbers</h5>
                            <table class="table tracking-table">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Added</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tracking in order.trackings">
                                    <td>
                                        <a :href="'http://sprtdls.aftership.com/'+tracking.number" target="_blank">
                                            {{tracking.number}}
                                        </a>
                                    </td>
                                    <td>{{tracking.added_at}}</td>
                                    <td>{{tracking.status}}</td>
                                    <td>{{tracking.status_at}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
    .tracking-table {
        font-size: 10px;
    }
</style>
