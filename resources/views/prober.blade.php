<pre>
<ol>    
        <li>Synced Redis · · · · · · · · · · · · · · · · · {{ $redis }}</li>
        <li>Successful transactions (last {{ $setting['prober_txns_orders'] }} orders) · · · · {{ $txns }}</li>
        <li>Firing (last {{ $setting['prober_firing_orders'] }} orders) · · · · · · · · · · · · {{ $firing }}</li>
</ol>
<b>{{ $result }}</b>
</pre>