<div class="container offer">
    <p><span class="bold">Special Offer:</span> {{$product->long_name}}</p>
    <p>Price:&nbsp;<span id="old-price" class="price-object productprice-old-object strike"> {{$product->prices['1']['old_value_text']}}</span>
        <span class="price-span">
          <b><span id="new-price" class="price-object productprice-object"> {{$product->prices['1']['value_text']}}</span></b>
        </span>&nbsp;(50% discount per unit)
    </p>
</div>
