<div
  class="pay-card-button"
  :class="{ active: !is_submitted }"
  @click="credit_card_create_order">

  <div
    v-if="is_submitted"
    class="disabled"
    @click.stop>
    @include('new.components.spinner')
  </div>

  @if (!empty($label))
    <div
      class="label"
      :class="{ hidden: is_submitted }">
      {!! $label !!}
    </div>
  @endif

  {!! $image ?? '' !!}

</div>
