<h1>Result</h1>
<table>
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


