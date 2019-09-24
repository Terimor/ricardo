<header
    id="header"
    class="
    header
        {{ isset($isTransparent) ? 'transparent' : '' }}
        {{ Request::is('/') ? 'header--menu' : '' }}
    "
>
    <div class="container {{ Request::is('/') ? 'd-flex flex-column flex-sm-row justify-content-between' : '' }}">
        <img src="{{$product->logo_image}}" alt="">
            @if(Request::is('/'))
                <ul class="header__menu">
                    <li class="header__menu-item">
                        <a href="/" class="header__menu-link header__menu-link--selected">
                            Home
                        </a>
                    </li>
                    <li class="header__menu-item">
                        <a href="#!" class="header__menu-link">
                            Our History
                        </a>
                    </li>
                    <li class="header__menu-item">
                        <a href="#!" class="header__menu-link">
                            Content page
                        </a>
                    </li>
                    <li class="header__menu-item">
                        <a href="/contact-us" class="header__menu-link">
                            Contact-us
                        </a>
                    </li>
                </ul>
            @endif

        <timer-component
            v-if="isTimerVisible" />
    </div>
</header>
