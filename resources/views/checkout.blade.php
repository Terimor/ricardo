@extends('layouts.app')

@section('content')
<app-component></app-component>


@if(isset($_GET['_payment_provider']) && $_GET['_payment_provider'] == 'bluesnap')
<br />
<form action="{{ route('bluesnap-payment') }}" method="POST">
    <div class="paper col-md-5 main__payment" style="float: right">
        <h2>Step 6: Payment Details</h2> 
        <label class="input-container card-number variant-1">
            <span class="label">Card Number</span>
            <div class="prefix"><img src="/images/card.png"></div>
            <div class="postfix"><i class="fa fa-lock"></i></div>
            <input name="card_number" value="4263982640269299" type="text" style="padding-left: 45px; padding-right: 45px;">
        </label>
        <div class="card-date">
            <span class="label">Card Valid Until</span>
            <div class="select variant-1">

                    <select name="expiration_month">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="12">12</option>
                    </select>
            </div>
            <div class="select variant-1">
                    <select name="expiration_year">
                        <option value="2019">2019</option>
                        <option value="2020">2020</option>
                    </select>
            </div>
                
        </div> 
        <label class="input-container cvv-field variant-1">
            <span class="label">CVV</span> <!----> 
            <div class="postfix"><i class="fa fa-question-circle"></i></div>
            <input name="security_code" type="text" style="padding-right: 45px;">
        </label>
        
        {{ csrf_field() }}
        <input type="submit" value="Send">
    </div>
    
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</form>
@endif

@endsection
