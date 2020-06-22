<button
  ref="pay_card_button"
  class="pay-card-button"
  :class="{ active: !is_submitted }"
>

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

</button>
