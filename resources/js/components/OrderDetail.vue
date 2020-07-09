<template>
    <tr class="order-info" v-if="isActive">
        <td colspan="4">
            <div class="row pt-1">
                <div class="col-md-6 border-right">
                    <h4>{{$t('support.order_info')}}</h4>
                    <div class="tab;e-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="pl-0">{{$t('support.product')}}</th>
                                <th>{{$t('support.paid')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="product in order.products" :class="product.is_paid ? '' : 'text-secondary font-weight-light font-italic'">
                                <td class="pl-0">{{product.quantity}} x {{product.name}}</td>
                                <td>{{product.price}}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th class="pl-0">{{$t('support.total_paid')}}</th>
                                <th>{{order.total_paid}}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{$t('support.shipping_address')}}</h5>
                            <p v-if="order.allowEditAddress"><button class="btn btn-primary" @click="editAddressClick">Edit address</button></p>
                            {{order.shipping_apt}} {{order.shipping_building}} {{order.shipping_street2}} {{order.shipping_street}} <br />
                            {{order.shipping_city}} {{order.shipping_state}} <br />
                            {{order.shipping_zip}} <br />
                            {{order.shipping_country_name}}

                        </div>
                        <div class="col-md-6">
                            <h5>{{$t('support.customer_information')}}</h5>

                                {{order.customer_first_name}} {{order.customer_last_name}} <br />
                                {{order.customer_phone}} <br />
                                {{order.customer_email}} <br />

                        </div>
                    </div>

                    <div class="row" v-if="order.trackings && order.trackings.length">
                        <div class="col-md-12 pt-4">
                            <h5>{{$t('support.tracking_numbers')}}</h5>
                            <table class="table tracking-table">
                                <thead>
                                <tr>
                                    <th class="pl-0">{{$t('support.number')}}</th>
                                    <th>{{$t('support.status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tracking in order.trackings">
                                    <td class="pl-0">
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
      editAddressClick() {
        this.$emit('editAddressClick', this.order)
      }
    }
  }
</script>

<style>
    .tracking-table {
        font-size: 10px;
    }
</style>
