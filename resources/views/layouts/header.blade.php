<header
    id="header"
    class="
        {{ isset($isTransparent) ? 'transparent' : '' }}
    "
>
    <div class="container">
        <img src="{{$product->logo_image}}" alt="">
        <timer-component></timer-component>
    </div>
</header>
