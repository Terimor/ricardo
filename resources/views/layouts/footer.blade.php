<footer class="footer {{ isset($isWhite) && $isWhite == true ? 'footer--white' : '' }}">
    <ul class="footer__row">
        @php
        $links = [
            [
                'text' => 'Contact us',
                'link' => '#!',
            ],
            [
                'text' => 'Terms of business',
                'link' => '#!',
            ],
            [
                'text' => 'Privacy',
                'link' => '#!',
            ],
            [
                'text' => 'Affiliate program',
                'link' => '#!',
            ]
        ];
        @endphp
        @foreach ($links as $item)
            <li class="footer__row-item">
                <a href="{{ $item['link'] }}" class="footer__link">{{ $item['text'] }}</a>
            </li>
        @endforeach
    </ul>
</footer>
