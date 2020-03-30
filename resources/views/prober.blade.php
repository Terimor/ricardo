<pre>
    <ol>
        <li>{{ str_pad('Synced Redis', 60, ' .', STR_PAD_RIGHT) }} {{ $redis }}</li>
        <li>{{ str_pad("Successful transactions (last {$setting['prober_orders_count']} orders)", 60, ' .', STR_PAD_RIGHT) }} {{ $success_orders }}%</li>
        <li>{{ str_pad("Firing (last {$setting['prober_firing_orders']} orders)", 60, ' .', STR_PAD_RIGHT) }} {{ $firing }}%</li>
        <li>Successful transactions (last {{$setting['prober_orders_count']}} orders)
            <ul>
                @foreach ($txn_result as $item)
                    <li style="color: {{$item['status'] ? 'black' : 'red'}}">{{ str_pad($item['name'], 55, ' .', STR_PAD_RIGHT) }} {{ $item['percent'] }}%</li>
                @endforeach
            </ul>
        </li>
    </ol>
    <b>{{ $result }}</b>
</pre>
