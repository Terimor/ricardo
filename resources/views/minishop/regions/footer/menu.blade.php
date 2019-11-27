<ul>
  @foreach ($footer_menu as $menu_item)
    <li>

      <a
        class="nav-link"
        href="{{ $menu_item['url'] }}">
        {!! isset($menu_item['phrase']) ? t($menu_item['phrase']) : ($menu_item['label'] ?? '') !!}
      </a>

    </li>
  @endforeach
</ul>
