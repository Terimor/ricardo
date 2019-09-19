@extends('layouts.app')

@section('title', 'Contacts')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/contact-us.css') }}">
@endsection


@section('content')
<div class="contacts">
    <div class="container">
        <div class="contacts__wrapper">
            <h1 class="contacts__main-title">
                Please feel free to contact us:
            </h1>
            <h3 class="contacts__sub-title">
                <br>
                By email:
            </h3>
            <a
                class="contacts__text contacts__text--link"
                href="mailto:help@support-deals.com"
            >
                help@support-deals.com
            </a>
            <h3 class="contacts__sub-title">
                By Phone:
            </h3>
            <p class="contacts__text">
                <strong>
                    USA/Canada
                </strong>
            </p>
            <a
                class="contacts__text contacts__text--link"
                href="tel:+ 1 351 888 2441"
            >
                + 1 351 888 2441
            </a>
            <p class="contacts__text">
                (24 hrs / Monday-Friday)
            </p>
            <h3 class="contacts__sub-title">
                International
            </h3>
            <a
                class="contacts__text contacts__text--link"
                href="tel:+ 44 178 245 4716"
            >
                + 44 178 245 4716
            </a>
            <p class="contacts__text">
                (English only – 24 hrs / Monday-Friday)
            </p>
            <br>
            <br>
            <h3 class="contacts__sub-title">
                By Mail:
            </h3>
            <p class="contacts__text">
                MDE Commerce Ltd.<br>
                72, TRIQ TAL-QROQQ,<br>
                MSIDA<br>
                Malta<br>
            </p>
            <h2 class="contacts__sub-title">
                THIS IS NOT A RETURN ADDRESS - FOR RETURNS PLEASE FIND ADDRESS <span class="contacts__text contacts__text--link">HERE</span>
            </h2>
            @include('layouts.footer', ['isWhite' => true])
        </div>
    </div>
</div>
@endsection
