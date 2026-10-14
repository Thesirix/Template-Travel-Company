<table>
    <thead>
    <tr>
        <th>NOM</th>
        <th>PRENOM</th>
        <th>GENRE</th>
        <th>DATE DE NAISSANCE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reservations as $res)
        @if($res->status == 1)
            @foreach($res->passagers as $pssgr)
                <tr>
                    <td>
                        {{$pssgr->nom}}
                    </td>
                    <td>
                        {{$pssgr->prenom}}
                    </td>
                    <td>
                        @if($pssgr->type == 'adulte')
                            @if($pssgr->genre == 'male')
                                MR
                            @else
                                MRS
                            @endif
                        @elseif($pssgr->type == 'enfant')
                            CHD
                        @else
                            INF
                        @endif
                    </td>
                    <td>
                        {{\Illuminate\Support\Carbon::parse($pssgr->date_naissance)->format('d/m/Y')}}
                    </td>
                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>
