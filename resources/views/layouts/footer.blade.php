
<footer class="footer">
    <ul class="footer__row">
        @php
        $links = [
            [
                'text' => 'Contact us',
                'link' => 'contacts',
            ],
            [
                'text' => 'Terms of business',
                'link' => 'terms',
            ],
            [
                'text' => 'Privacy',
                'link' => 'privacy',
            ],
            [
                'text' => 'Affiliate program',
                'link' => 'affiliate',
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
