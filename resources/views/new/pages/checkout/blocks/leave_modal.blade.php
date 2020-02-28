<div
  v-cloak
  v-if="leave_modal_visible"
  class="leave-modal">

  <div class="inside">

    <img
      data-src="{{ $product->logo_image ?? '' }}"
      class="leave-modal-logo lazy"
      alt="" />

    {!! t('exit_popup.text', ['count' => $deals_main_quantities[$deal_promo['quantity']] ?? 0, 'amount' => $deals_free_quantities[$deal_promo['quantity']] ?? 0, 'payment_details' => $deal_promo['value_text'] ?? '']) !!}

    <button
      class="leave-modal-offer-btn"
      @click="leave_modal_agree_click">
      {!! t('exit_popup.agree') !!}
    </button>

    <button
      class="leave-modal-close-btn"
      @click="leave_modal_close_click">
      {!! t('exit_popup.close') !!}
    </button>

  </div>

</div>
