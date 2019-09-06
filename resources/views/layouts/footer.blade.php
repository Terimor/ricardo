
<footer class="footer">
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
            <li>
                <a href="{{ $item['text'] }}" class="footer__link">{{ $item['text'] }}</a>
            </li>
        @endforeach
    </ul>
</footer>
