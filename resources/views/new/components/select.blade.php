<div
  ref="{{ $name }}_field"
  class="select {{ $name }}-field scroll-when-error"
  :class="{ invalid: {{ $validation }} && {{ $validation }}.$dirty && {{ $validation }}.$invalid }"
  @if (!empty($init)) v-if="{{ $init }}() || true" @endif>

  <div class="select-field-label">
    {!! !empty($label_code) ? '@{{ ' . $label_code . ' }}' : '' !!}
    {!! !empty($label) ? $label : '' !!}
  </div>

  <div class="select-field-container">

    @if (!empty($loading))
      <div
        v-if="{{ $loading }}"
        class="select-field-loading">
        @include('new.components.spinner')
      </div>
    @endif

    <select
      class="select-field-input"
      @input.touch="input_touch({{ $validation }})"
      @if (!empty($model)) v-model="{{ $model }}" @endif
      @if (!empty($change)) @change="{{ $change }}" @endif
    >

      @if (!empty($placeholder) || !empty($placeholder_code))
        <option
          :value="null"
          style="display:none">
          {!! !empty($placeholder_code) ? '@{{ ' . $placeholder_code . ' }}' : '' !!}
          {!! !empty($placeholder) ? $placeholder : '' !!}
        </option>
      @endif

      @if (!empty($items))
        <template v-if="!{{ $items_code ?? 'false' }}">
          @foreach ($items as $item)
            <option value="{{ $item['value'] }}">{!! $item['label'] !!}</option>
          @endforeach
        </template>
      @endif

      @if (!empty($items_code))
        <option
          v-for="item in {{ $items_code }}"
          :value="item.value"
          v-html="item.label">
        </option>
      @endif

    </select>

  </div>

  <div
    v-if="{{ $validation }} && {{ $validation }}.$dirty"
    class="select-field-errors">

    @foreach ($validation_labels ?? [] as $vname => $vlabel)
      <div
        v-if="!{{ $validation }}.{{ $vname }}"
        class="select-field-error $v-{{ $vname }}-error">{!! $vlabel !!}</div>
    @endforeach

  </div>

</div>
