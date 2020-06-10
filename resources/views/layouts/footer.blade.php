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
        ];
        @endphp
        @if (isset($hasHome) && $hasHome == true)
            <li class="footer__row-item">
                <a href="/" class="footer__link">{{ t('footer.home') }}</a>
            </li>
        @endif
        @foreach ($links as $item)
            <li class="footer__row-item">
                <a href="{{ $item['link'] }}" class="footer__link">{{ $item['text'] }}</a>
            </li>
        @endforeach
        <br/>
        @if (!isset($aff['is_signup_hidden']) || !$aff['is_signup_hidden'])
            <li class="footer__row-item">
                <a href="https://www.h8m8.com" target="_blank" class="footer__link">{{ t('footer.affiliate') }}</a>
            </li>
        @endif
        <li class="footer__row-item">
            <a href="/delivery" class="footer__link">{{ t('footer.delivery') }}</a>
        </li>
        <li class="footer__row-item">
            <a href="/report-abuse" class="footer__link">{{ t('abuse.title') }}</a>
        </li>
    </ul>
    @if ($is_aff_id_empty && (!isset($isCompanyHidden) || $isCompanyHidden == false))
        <div class="company-address">{{ $company_address }}</div>
    @endif
</footer>
