
@if(is_array($info))
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Order number</th>
            <th scope="col">Order products</th>
            <th scope="col">Tracking website</th>
        </tr>
        </thead>
        <tbody>
        @foreach($info as $i)
            <tr>
                <th scope="row">{{ $loop->iteration }}</th>
                <td>{{ $i['order_number'] }}</td>
                <td>{!! $i['products'] !!}</td>
                <td><a target="_blank" href="{{ $i['link'] }}">{{ $i['link'] }}</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@elseif(is_string($info))
    <div class="alert alert-warning" role="alert">
        {{ $info }}
    </div>
@endif