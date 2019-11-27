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

  @if (!$is_signup_hidden)
    <li>
      <a
        class="nav-link"
        href="https://www.h8m8.com">
        {!! t('minishop.menu.affiliate') !!}
      </a>
    </li>
  @endif

</ul>
