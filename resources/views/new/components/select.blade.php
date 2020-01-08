<div
  ref="{{ $name }}_field"
  class="select {{ $name }}-field scroll-when-error"
  :class="{ invalid: {{ $validation }}.$dirty && {{ $validation }}.$invalid }"
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
      @if (!empty($model)) v-model="{{ $model }}" @endif
      @if (!empty($change)) @change="{{ $change }}" @endif
      @change.touch="{{ $validation }}.$touch">

      @if (!empty($placeholder))
        <option
          :value="null"
          style="display:none">
          {!! !empty($label_code) ? '@{{ ' . $label_code . ' }}' : '' !!}
          {!! !empty($label) ? $label : '' !!}
        </option>
      @endif

      <option
        :key="item.value"
        v-for="item in {{ $items }}"
        :value="item.value"
        v-html="item.label" />

    </select>

  </div>

  <div
    v-if="{{ $validation }}.$dirty"
    class="select-field-errors">

    @foreach ($validation_labels ?? [] as $vname => $vlabel)
      <div
        v-if="!{{ $validation }}.{{ $vname }}"
        class="select-field-error $v-{{ $vname }}-error">{!! $vlabel !!}</div>
    @endforeach

  </div>

</div>
