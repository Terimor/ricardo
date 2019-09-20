<footer class="footer {{ isset($isWhite) && $isWhite == true ? 'footer--white' : '' }}">
    <ul class="footer__row">
        @php
        $links = [
            [
                'text' => t('footer.contact'),
                'link' => '/contact-us',
            ],
            [
                'text' => t('footer.terms'),
                'link' => '/terms',
            ],
            [
                'text' => t('footer.privacy'),
                'link' => '/privacy',
            ],
            [
                'text' => t('footer.affiliate'),
                'link' => '#!',
            ]
        ];
        @endphp
        @if (isset($hasHome) && $hasHome == true)
            <li class="footer__row-item">
                <a href="/" class="footer__link">Home</a>
            </li>
        @endif
        @foreach ($links as $item)
            <li class="footer__row-item">
                <a href="{{ $item['link'] }}" class="footer__link">{{ $item['text'] }}</a>
            </li>
        @endforeach
    </ul>
</footer>
