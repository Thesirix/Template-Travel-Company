<table>
    <thead>
    <tr>
        <td style="width:8px;background-color: #2e2ec4;color: white">Genre</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Nom</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Prenom</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Statut</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Date de naissance</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">N Passport</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Expire</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">N Titre Séjour</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Expire</td>

        <td style="width: 15px;background-color: #2e2ec4;color: white">tele1</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">tele2</td>
        <td style="width: 15px;background-color: #2e2ec4;color: white">Tél. sur place</td>

<td style="width: 15px;background-color: #2e2ec4;color: white">Vérifier</td>
    </tr>
    </thead>
    <tbody>
    @foreach($reservations as $res)
        @if($res->status == 1)
            @foreach($res->passagers as $pssgr)

                <tr>
                    <td>
                        @if($pssgr->genre == 'male')
                            M
                        @else
                            F
                        @endif
                    </td>
                    <td>
                        {{$pssgr->nom}}
                    </td>
                    <td>
                        {{$pssgr->prenom}}
                    </td>
                    <td>
                        {{$pssgr->type}}
                    </td>
                    <td>
                        {{\Illuminate\Support\Carbon::parse($pssgr->date_naissance)->format('d-m-Y')}}
                    </td>
                    <td>
                        {{$pssgr->passport}}
                    </td>
                    <td>
                        {{\Illuminate\Support\Carbon::parse($pssgr->expiration)->format('d-m-Y')}}
                    </td>

                    <td>
                        {{$pssgr->sijour}}
                    </td>
                    <td>
                        {{\Illuminate\Support\Carbon::parse($pssgr->sijour_expiration)->format('d-m-Y')}}

                    </td>
                    <td>
                        {{$pssgr->tele1}}
                    </td>
                    <td>
                        {{$pssgr->tele2}}
                    </td>
                    <td>
                        {{$pssgr->tele_destination}}
                    </td>
                    <td>
                        {{$pssgr->verifier}}
                    </td>

                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>
