<div
  ref="fixed"
  class="fixed-region"
  :class="{ fixed: window_scroll_top > 0 }">

  @include('new.fixed.covid')
  @include('components.static_topbar')
  @include('minishop.regions.fixed.support')
  @yield('fixed')

</div>
