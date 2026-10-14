<table>
    <thead>
    <tr>
        <th>phone</th>
    </tr>
    </thead>
    <tbody>
    @foreach($phones as $phone)
        <tr>
            <td>{{ $phone }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
