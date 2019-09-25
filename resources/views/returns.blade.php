@extends('layouts.app')

@section('title', $product->page_title . ' - ' . $loadedPhrases['refunds_title'])

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/static.css') }}">
@endsection


@section('content')
<div class="static">
    <div class="container">
        <div class="static__wrapper">
            <h1>
                <strong>
                    Refund Policy
                </strong>
                <p>
                    <br>
                    RETURNS
                </p>
            </h1>
            <p>
                ---
            </p>
            <p>
                Our policy lasts 30 days. If 30 days have gone by since your delivery, unfortunately we can’t offer you a refund or exchange.
            </p>
            <p>
                To be eligible for a return, your item must be unused and in the same condition that you received it.
                It must also be in the original packaging and sent with a tracking code.
            </p>
            <p>
                Several types of goods are exempt from being returned. Perishable goods such as food, flowers, newspapers or magazines cannot be returned.
                We also do not accept products that are intimate or sanitary goods, hazardous materials, or flammable liquids or gases.
            </p>
            <p>
                Additional non-returnable items:
            </p>
            <p>
                * Gift cards
            </p>
            <p>
                * Used cleaning/hygiene items
            </p>
            <p>
                * Downloadable software products
            </p>
            <p>
                * Some health and personal care items
            </p>
            <p>
                To complete your return, we require a receipt or proof of purchase.
            </p>
            <p>
                There are certain situations where only partial refunds are granted: (if applicable)
            </p>
            <p>
                * Any item not in its original condition, is damaged or missing parts for reasons not due to our error.
            </p>
            <p>
                * Any item that is returned more than 30 days after delivery
            </p>
            <p>
                <strong>
                    Refunds (if applicable)
                </strong>
            </p>
            <p>
                Once your return is received and inspected, we will send you an email to notify you that we have received your returned item.
                We will also notify you of the approval or rejection of your refund.
            </p>
            <p>
                If you are approved, then your refund will be processed, and a credit
                will automatically be applied to your credit card or original method of payment, within a certain amount of days.
            </p>
            <p>
                <strong>
                    Late or missing refunds (if applicable)
                </strong>
            </p>
            <p>
                If you haven’t received a refund yet, first check your bank account again.
            </p>
            <p>
                Then contact your credit card company, it may take some time before your refund is officially posted.
            </p>
            <p>
                Next contact your bank. There is often some processing time before a refund is posted.
            </p>
            <p>
                If you’ve done all of this and you still have not received your refund yet, please contact us at <strong><a href="mailto:help@deals-support.com">help@deals-support.com</a></strong>
            </p>
            <p>
                <strong>
                    Sale items (if applicable)
                </strong>
            </p>
            <p>
                <strong>
                    Exchanges (if applicable)
                </strong>
            </p>
            <p>
                We only replace items if they are defective or damaged.
                If you need to exchange it for the same item, send us an email at <strong><a href="mailto:help@deals-support.com">help@deals-support.com</a></strong>
            </p>
            <br>
            <p>
                <strong>
                    Gifts
                </strong>
            </p>
            <p>
                If the item was marked as a gift when purchased and shipped directly to you, you’ll receive a gift credit for the value of your return.
                Once the returned item is received, a gift certificate will be mailed to you.
                If the item wasn’t marked as a gift when purchased, or the gift giver had the order shipped to themselves to give to you later,
                we will send a refund to the gift giver and he will find out about your return.
            </p>
            <p>
                <strong>
                    Shipping
                </strong>
            </p>
            <p>
                To return your product, please send us an email at <strong><a href="mailto:help@deals-support.com">help@deals-support.com</a></strong> so we can provide you the shipping address.
            </p>
            <br>
            <p>
                You will be responsible for paying for your own shipping costs for returning your item with registered mail. Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted from your refund.
            </p>
            <p>
                Depending on where you live, the time it may take for your exchanged product to reach you, may vary.
            </p>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center;">
                <strong>
                    "In case of discrepancies, the original English version prevails"
                </strong>
            </p>


            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
