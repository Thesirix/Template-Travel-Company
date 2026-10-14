<table>
    <thead>
    <tr>
        <th>LAST_NAME</th>
        <th>FIRST_NAME</th>
        <th>TITLE</th>
        <th>PTC</th>
        <th>GENDER</th>
        <th>DATE_OF_BIRTH</th>
        <th>PASSPORT_LAST_NAME</th>
        <th>PASSPORT_FIRST_NAME</th>
        <th>PASSPORT_NUMBER</th>
        <th>PASSPORT_NATIONALITY</th>
        <th>PASSPORT_ISSUE_COUNTRY</th>
        <th>PASSPORT_EXPIRY_DATE</th>
        <th>VISA_NUMBER</th>
        <th>VISA_TYPE</th>
        <th>VISA_ISSUE_DATE</th>
        <th>PLACE_OF_BIRTH</th>
        <th>VISA_PLACE_OF_ISSUE</th>
        <th>VISA_COUNTRY_OF_APPLICATION</th>
        <th>VISA_EXPIRY_DATE</th>
        <th>ADDRESS_TYPE</th>
        <th>ADDRESS_COUNTRY</th>
        <th>ADDRESS_DETAILS</th>
        <th>ADDRESS_CITY</th>
        <th>ADDRESS_STATE</th>
        <th>ADDRESS_ZIP_CODE</th>
        <th>FQTV_CARRIER_1</th>
        <th>FQTV_NUMBER_1</th>
        <th>FQTV_CARRIER_2</th>
        <th>FQTV_NUMBER_2</th>
        <th>FQTV_CARRIER_3</th>
        <th>FQTV_NUMBER_3</th>
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

                        @if($pssgr->genre == 'male')
                            MR
                        @else
                            MRS
                        @endif
                    </td>
                    <td>
                        @if($pssgr->type == 'adulte')
                            ADT
                        @elseif($pssgr->type == 'enfant')
                            CHD
                        @else
                            INF
                        @endif
                    </td>
                    <td>
                        @if($pssgr->genre == 'male')
                            M
                        @else
                            F
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
