<footer class="footer {{ isset($isWhite) && $isWhite == true ? 'footer--white' : '' }}">
    <ul class="footer__row">
        @php
        $links = [
            [
                'text' => t('footer.contact'),
                'link' => '#!',
            ],
            [
                'text' => t('footer.terms'),
                'link' => '#!',
            ],
            [
                'text' => t('footer.privacy'),
                'link' => '#!',
            ],
            [
                'text' => t('footer.affiliate'),
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
