<ul class="navbar-nav ml-auto mr-auto mt-2 mt-lg-0">

  @foreach ($header_menu as $menu_item)
    <li class="nav-item{{ Route::is($menu_item['active']) ? ' active' : '' }}">
      <a
        class="nav-link"
        href="{{ $menu_item['url'] }}">{{ t($menu_item['label']) }}</span>
      </a>
    </li>
  @endforeach

</ul>
