<template>
  <div
    v-if="leave_modal_visible"
    class="leave-modal"
    @click="leave_modal_close_click">

    <div
      class="inside"
      @click.stop>

      <div class="line1">{!! t('exit_popup.line1') !!}</div>
      <div class="line2">{!! t('exit_popup.line2') !!}</div>
      <div class="line3">{!! $product->splash_description . ' - ' . t('exit_popup.line3') !!}</div>
      <div class="line4">{!! t('exit_popup.line4', ['product' => $product->product_name]) !!}</div>
      <div class="button" @click="leave_modal_agree_click">{!! t('exit_popup.button') !!}</div>
      <div class="link" @click="leave_modal_close_click">{!! t('exit_popup.link') !!}</div>

    </div>

  </div>
</template>
