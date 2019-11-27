<ul>

  @foreach ($footer_menu as $menu_item)
    <li>
      <a
        class="nav-link"
        href="{{ $menu_item['url'] }}">{{ t($menu_item['label']) }}</a>
    </li>
  @endforeach

</ul>
