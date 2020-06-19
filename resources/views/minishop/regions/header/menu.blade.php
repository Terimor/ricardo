<ul class="navbar-nav ml-auto mt-2 mt-lg-0">
  @foreach ($header_menu as $menu_item)
    @if (!($product->type == 'virtual' && $menu_item['phrase'] == 'minishop.menu.contact_us'))
      <li
        class="nav-item{{ isset($menu_item['class']) ? ' ' . $menu_item['class'] : '' }}{{ isset($menu_item['active']) && Route::is($menu_item['active']) ? ' active' : '' }}">

        <a
          class="nav-link d-flex"
          {!! isset($menu_item['url']) ? 'href="' . $menu_item['url'] . '"' : '' !!}>
          {!! isset($menu_item['phrase']) ? t($menu_item['phrase']) : ($menu_item['label'] ?? '') !!}
          @if (isset($menu_item['icon']))
            &nbsp;
            <img
              src="{{ $cdn_url . '/assets/images/' . $menu_item['icon'] }}"
              width="24"
              height="24"
              class="d-inline-block align-top"
              alt="" />
          @endif
        </a>

        @if (isset($menu_item['submenu']))
          <div class="dropdown-menu">
            @foreach ($menu_item['submenu'] as $submenu_item)
              <a
                class="dropdown-item"
                {!! isset($submenu_item['url']) ? 'href="' . $submenu_item['url'] . '"' : '' !!}>
                {!! isset($submenu_item['phrase']) ? t($submenu_item['phrase']) : ($submenu_item['label'] ?? '') !!}
              </a>
            @endforeach
          </div>
        @endif

      </li>
    @endif
  @endforeach

</ul>
