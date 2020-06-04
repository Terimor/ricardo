<div
  @if (!empty($ref)) ref="{{ $ref }}" @endif
  v-if="{{ !empty($active) ? $active : 'true' }}"
  class="error{{ !empty($class) ? ' ' . $class : '' }}"
  v-cloak
>
    
  {!! !empty($label_code) ? '@{{ ' . $label_code . ' }}' : '' !!}
  {!! !empty($label) ? $label : '' !!}

</div>
