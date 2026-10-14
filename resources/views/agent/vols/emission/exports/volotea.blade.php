<table>
    <thead>
    <tr>
        <td style="width:150px">Dossier Id</td>
        <td style="width:150px;">Passenger Type</td>
        <td style="width:150px;">Gender</td>
        <td style="width:150px;">Title</td>
        <td style="width:150px;">First Name</td>
        <td style="width:150px;">Last Name</td>
        <td style="width:150px;">Date of Birth</td>
    </tr>
    </thead>
    <tbody>
    @foreach($reservations as $res)
        @if($res->status == 1)
            @foreach($res->passagers as $pssgr)

                <tr>
                    <td>
                        {{$res->id}}
                    </td>
                    <td>
                        @if($pssgr->type == 'adulte')
                            Adult
                        @elseif($pssgr->type == 'enfant')
                            Chd
                        @else
                            Inf
                        @endif
                    </td>
                    <td>
                        @if($pssgr->genre == 'male')
                            Male
                        @else
                            Female
                        @endif
                    </td>
                    <td>

                        @if($pssgr->genre == 'male')
                            Mr
                        @else
                            @if($pssgr->type == 'adulte')
                                Mrs
                            @else
                                Miss
                            @endif
                        @endif
                    </td>
                    <td>
                        {{$pssgr->prenom}}
                    </td>
                    <td>
                        {{$pssgr->nom}}
                    </td>
                    <td>
                        @if($pssgr->type != 'adulte')
                            {{\Illuminate\Support\Carbon::parse($pssgr->date_naissance)->format('d/m/Y')}}
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>
