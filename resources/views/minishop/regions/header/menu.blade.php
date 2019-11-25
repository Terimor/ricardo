<ul class="navbar-nav ml-auto mr-auto mt-2 mt-lg-0">

  @foreach ($header_menu as $menu_item)
    <li class="nav-item{{ $menu_item['active'] ? ' active' : '' }}">
      <a
        class="nav-link"
        href="{{ $menu_item['url'] }}">{{ $menu_item['label'] }}</span>
      </a>
    </li>
  @endforeach

</ul>
