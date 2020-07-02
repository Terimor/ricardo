<template>
    <li class="list-group-item order-heading">
        Number: {{order.number}}, Date: {{order.created_at}}, Total: {{order.total_paid}} {{order.currency}}
        <div class="order-info">
            <div class="row">
                <div class="col-md-6">
                    <h4>Order Info</h4>
                    {{order.customer_first_name}}<br>
                    {{order.customer_last_name}}<br>
                    {{order.customer_phone}}<br>
                    {{order.status}}<br>
                    {{order.total_price}} {{order.currency}}<br>

                    <div>

                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Shipping info</h4>
                    {{order.shipping_country}}<br>
                    {{order.shipping_zip}}<br>
                    {{order.shipping_state}}<br>
                    {{order.shipping_city}}<br>
                    {{order.shipping_street}}<br>
                    {{order.shipping_street2}}<br>
                    {{order.shipping_building}}<br>
                    {{order.shipping_apt}}<br>
                    {{order.shipping_apt}}<br>
                    <template v-if="order.trackings && order.trackings.length">
                        <h4>Tracking numbers</h4>
                        <ul>
                            <li v-for="tracking in order.trackings"><a href="">{{tracking.number}}-{{tracking.status}}</a></li>
                        </ul>
                    </template>

                    <h4> Products </h4>
                    <ul>
                        <li v-for="product in order.products">{{product.quantity}} x {{product.sku_code}}</li>
                    </ul>


                </div>
            </div>

        </div>
    </li>

</template>

<script>
  export default {
    name: "OrderDetail",
    props: ['order', 'isActive'],
  }
</script>

<style>

</style>
