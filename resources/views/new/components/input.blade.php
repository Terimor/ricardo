<div
  ref="{{ $name }}_field"
  class="input {{ $name }}-field scroll-when-error{{ !empty($prefix) ? ' with-prefix' : '' }}{{ !empty($suffix) ? ' with-suffix' : '' }}"
  :class="{ invalid: {{ $validation }}.$dirty && {{ $validation }}.$invalid }"
  @if (!empty($init)) v-if="{{ $init }}() || true" @endif>

  <div class="input-field-label">
    {!! !empty($label_code) ? '@{{ ' . $label_code . ' }}' : '' !!}
    {!! !empty($label) ? $label : '' !!}
  </div>

  <div class="input-field-container">

    @if (!empty($loading))
      <div
        v-if="{{ $loading }}"
        class="input-field-loading">
        @include('new.components.spinner')
      </div>
    @endif

    @if (!empty($prefix))
      <div
        class="input-field-prefix{{ !empty($prefix_click) ? ' clickable' : '' }}"
        @if (!empty($prefix_click)) @click="{{ $prefix_click }}" @endif>
        {!! $prefix !!}
      </div>
    @endif

    @if (!empty($suffix))
      <div
        class="input-field-suffix{{ !empty($suffix_click) ? ' clickable' : '' }}"
        @if (!empty($suffix_click)) @click="{{ $suffix_click }}" @endif>
        {!! $suffix !!}
      </div>
    @endif

    @if (!empty($mask))
      <div
        class="input-field-mask"
        v-html="{{ $mask }}"></div>
    @endif

    <input
      type="text"
      class="input-field-input{{ !empty($monospace) ? ' monospace' : '' }}"
      @input.touch="input_touch({{ $validation }})"
      @if (!empty($placeholder_code)) :placeholder="{{ $placeholder_code }}" @endif
      @if (!empty($placeholder)) placeholder="{{ $placeholder }}" @endif
      @if (!empty($model)) v-model="{{ $model }}" @endif
      @if (!empty($input)) @input="{{ $input }}" @endif
      @if (!empty($focus)) @focus="{{ $focus }}" @endif
      @if (!empty($blur)) @blur="{{ $blur }}" @endif
    />

  </div>

  <div
    v-if="{{ $validation }}.$dirty"
    class="input-field-errors">

    @foreach ($validation_labels ?? [] as $vname => $vlabel)
      <div
        v-if="!{{ $validation }}.{{ $vname }}"
        class="input-field-error $v-{{ $vname }}-error">{!! $vlabel !!}</div>
    @endforeach

    @foreach ($extra_validation_labels ?? [] as $vname => $vlabel)
      <div
        v-if="extra_validation['{{ $validation }}.{{ $vname }}']"
        class="input-field-error ev-{{ $vname }}-error">{!! $vlabel !!}</div>
    @endforeach

  </div>

</div>
