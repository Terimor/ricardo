<header
    id="header"
    class="
        header
        {{ isset($isTransparent) ? 'transparent' : '' }}
        {{ Request::is('/') ? 'header--menu' : '' }}
    "
>
    <div class="container {{ Request::is('/') ? 'd-flex flex-column flex-sm-row justify-content-between' : '' }}">
        <a href="/checkout" class="header__logo_link">
            <img src="{{$product->logo_image}}" class="header__logo" alt="">
        </a>
        @if(Request::is('/'))
            <ul class="header__menu">
                <li class="header__menu-item">
                    <a href="/" class="header__menu-link header__menu-link--selected">
                        {{ t('footer.home') }}
                    </a>
                </li>
                <li class="header__menu-item">
                    <a href="/about" class="header__menu-link">
                        {{ t('footer.about') }}
                    </a>
                </li>
                <li class="header__menu-item">
                    <a href="/contact-us" class="header__menu-link">
                        {{ t('footer.contact') }}
                    </a>
                </li>
            </ul>
        @endif

        @if((Route::is('checkout') || Route::is('checkout_price_set')) && (Request::get('show_timer') === '{timer}' || Request::get('show_timer') === '1'))
          <timer-component />
        @endif
    </div>
</header>
