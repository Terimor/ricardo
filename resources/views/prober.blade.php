<pre>
<ol>
    <?php foreach($results as $result): ?>
        <li>{{ $result['name'] }} · · · · · · · · · · · · {{ $result['status'] }} - {{ $result['result'] }}</li>
    <?php endforeach; ?>
</ol>
</pre>