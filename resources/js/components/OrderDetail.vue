<template>
    <tr class="order-info" v-if="isActive">
        <td colspan="4">
            <div class="row">
                <div class="col-md-6 border-right">
                    <h4>Order Info</h4>
                    <div class="tab;e-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Product</th>

                                <th>Paid</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="product in order.products" :class="product.is_paid ? '' : 'text-secondary font-weight-light font-italic'">
                                <td>{{product.quantity}} x {{product.name}}</td>
                                <td>{{product.total}}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total Paid</th>
                                <th>{{order.total_paid}}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Shipping address</h5>
                            {{order.shipping_apt}} {{order.shipping_building}} {{order.shipping_street2}} {{order.shipping_street}} <br />
                            {{order.shipping_city}} {{order.shipping_state}} <br />
                            {{order.shipping_zip}} <br />
                            {{order.shipping_country}}

                        </div>
                        <div class="col-md-6">
                            <h5>Customer information</h5>

                                {{order.customer_first_name}} {{order.customer_last_name}} <br />
                                {{order.customer_phone}} <br />
                                {{order.customer_email}} <br />

                        </div>
                    </div>


                    <div class="row" v-if="order.trackings && order.trackings.length">
                        <div class="col-md-12 pt-4">
                            <h5>Tracking numbers</h5>
                            <table class="table tracking-table">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tracking in order.trackings">
                                    <td>
                                        <a :href="'http://sprtdls.aftership.com/'+tracking.number" target="_blank">
                                            {{tracking.number}}
                                        </a>
                                    </td>
                                    <td>{{tracking.status}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </td>


    </tr>

</template>

<script>
  export default {
    name: "OrderDetail",
    props: ['order', 'isActive'],
    methods: {

    }
  }
</script>

<style>
    .tracking-table {
        font-size: 10px;
    }
</style>
