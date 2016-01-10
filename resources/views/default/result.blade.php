<h2>Result</h2>
@if($multi === false)
<table class="table">
    <thead>
        <tr>
            <th>String</th>
            <th>Diff</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($result as $item)
        <tr>
            <td>{{ $item['line'] }}</td>
            <td>{{ $item['diff'] }}</td>
            <td>{{ $item['value'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@else

@foreach($result as $item)
<table class="table pull-left" style="width: {{ 100/(count($result) % 100) }}%">
    <thead>
        <tr>
            <th>String</th>
            <th>Diff</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($item as $row)
        <tr>
            <td>{{ $row['line'] }}</td>
            <td>{{ $row['diff'] }}</td>
            <td>{{ $row['value'] }}</td>
        </tr>
        @endforeach
    </tbody>

</table>
@endforeach
@endif

