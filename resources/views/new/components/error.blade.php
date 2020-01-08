<div
  @if (!empty($ref)) ref="{{ $ref }}" @endif
  v-if="{{ !empty($active) ? $active : 'true' }}"
  class="error{{ !empty($class) ? ' ' . $class : '' }}"
  v-html=@json($label)></div>
