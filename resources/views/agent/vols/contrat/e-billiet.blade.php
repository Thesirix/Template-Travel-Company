<!DOCTYPE html>
<html>

<head>
    <meta name="robots" content="noindex">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style>
        @page {
            margin: 10px;
        }

        body {
            margin: 10px;
        }

        * {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        p {
            margin-bottom: 0;
            margin-top: 0;
        }

        table {
            width: 100%;
        }

        .table, .table tr, .table td, .table th {
            border: solid 1px grey;
            border-collapse: collapse;
        }

        .table td, .table td {
            padding-top: 3px;
            padding-bottom: 3px;
            text-align: center;
        }


        /*.section {*/
        /*    page-break-before: avoid;*/
        /*    page-break-after: avoid;*/

        /*}*/
    </style>
</head>
<body>
<table style="width: 600px;margin-bottom: 20px">
    <tr>
        <td>
            <img src="{{ \App\Support\PdfAsset::path($reservation->gent->agence->image) }}" style="width: 150px;">
        </td>
        <td style="font-size: 22px">
            Reservation confirmée
        </td>
    </tr>
</table>
<div class="section" style="clear: both">
    <table style="width: 600px;margin-bottom: 10px">
        <tr>
            <td style="width: 200px;height: 30px;margin-bottom:0;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                RESERVATION
            </td>
            <td style="font-size: 12px;padding-left: 20px;font-weight: bold">
                <span style="margin-right: 20px">PNR:</span> {{$reservation->vol->pnr}}
            </td>
        </tr>
    </table>

    <p class="titre">Nombre de participants au voyage : {{$reservation->passagers->count()}}</p>
    <table class="table passager-table">
        <tr>
            <th>Titre</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Catégorie</th>
        </tr>
        @foreach($reservation->passagers as $person)
            <tr>
                <td>@if($person->genre=='male')
                        Monsieur
                    @else
                        Madame
                    @endif</td>
                <td>{{$person->nom}}</td>
                <td> {{$person->prenom}}</td>
                <td> {{$person->type}}</td>
            </tr>
        @endforeach


    </table>
</div>

<div class="section" style="clear: both;margin-top: 20px">
    <table style="width: 700px;margin-bottom: 10px">
        <tr>
            <td style="width: 200px;height: 30px;margin-bottom:0;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                TRANSPORT
            </td>
            <td style="font-size: 12px;padding-left: 20px;font-weight: bold">
                Merci de confirmer les horaires de vol 24h avant le depart ALLER et RETOUR
            </td>
        </tr>
    </table>
    <div>
        @if($reservation->type_voyage != 'retour_simple')
            <table>
                <tr>
                    <td style="font-size: 16px;width: 150px">
                        <span style="color: #fe6219;">Aller</span>
                        {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                    </td>
                    <td>
                        <div style="border: solid 2px #fe6219;width: 400px;padding: 7px 7px 7px 7px;">
                            <p>Presentation 3h avant depart</p>
                            <table>
                                <tr>
                                    <td>
                                        <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                             style="height: 30px;">
                                    </td>
                                    <td>
                                        <p style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',0)->first()->airPortDe->abv}}
                                        </p>
                                        <p> {{\Carbon\Carbon::parse($reservation->vol->heur_depart_aller)->format("H:i")}}</p>
                                    </td>
                                    <td>
                                        <p>
                                            {{$durationAller->format('H').' h '.$durationAller->format('i').' min'}}
                                        </p>
                                        <img src="{{ \App\Support\PdfAsset::path('sys/right-arrow.png') }}"
                                             style="width: 50px;height: 10px">
                                        <p>@if(count($reservation->vol->escaliers->where('type',0))>1)
                                                {{count($reservation->vol->escaliers->where('type',0))}}
                                                Escales
                                            @else
                                                Direct
                                            @endif</p>
                                    </td>
                                    <td>
                                        <p style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',0)->last()->airPortA->abv}}
                                        </p>
                                        <p class="mb-0">
                                            {{\Carbon\Carbon::parse($reservation->vol->heur_arriver_aller)->format('H:i')}}
                                        </p>

                                    </td>
                                    <td style="font-size: 20px">
                                        {{$reservation->vol->escaliers->where('type',0)->first()->model}}
                                    </td>
                                </tr>
                            </table>
                            @if(count($reservation->vol->escaliers->where('type',0))>1)
                                @foreach($reservation->vol->escaliers->where('type',0) as $escalier)
                                    <table>
                                        <tr>
                                            <td>
                                                <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}" style="height: 30px"
                                                     class="mr-3">
                                                <p class="" style="font-size: 10px;">
                                                    {{$escalier->model}}
                                                </p>
                                            </td>
                                            <td style="width: 70%">
                                                <p style="color: #0b97c4;">
                                                    {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d m Y')}}
                                                </p>
                                                <table>
                                                    <tr>
                                                        <td>Départ</td>
                                                        <td style="">
                                                            <b>{{$escalier->heure_depart}}</b>
                                                        </td>
                                                        <td style="color:#fe6219 ">{{$escalier->airPortDe->abv}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Arrivée</td>
                                                        <td><b> {{$escalier->heure_arriver}}</b></td>
                                                        <td style="color:#fe6219 ">{{$escalier->airPortA->abv}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Temps vol</td>
                                                        <td><b> {{$escalier->temp}}</b></td>
                                                    </tr>

                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
    <div>
        @if($reservation->type_voyage != 'aller_simple')
            @if($reservation->type_voyage=='aller_retour_same_vol')
                <table style="margin-top: 20px">
                    <tr>
                        <td style="font-size: 16px;width: 150px">
                            <span style="color: #fe6219">Retour</span>
                            {{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}
                        </td>
                        <td>
                            <div style="border: solid 2px #fe6219;width: 400px;padding: 7px 7px 7px 7px;">
                                <p>Presentation 3h avant depart</p>
                                <table>
                                    <tr>
                                        <td>
                                            <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                                 style="height: 30px;">
                                        </td>
                                        <td>
                                            <p style="color: #fe6219">
                                                {{$reservation->vol->escaliers->where('type',1)->first()->airPortDe->abv}}
                                            </p>
                                            <p>
                                                {{\Carbon\Carbon::parse($reservation->vol->heur_depart_retour)->format("H:i")}}
                                            </p>
                                        </td>
                                        <td>
                                            <p>
                                                {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                            </p>
                                            <img src="{{ \App\Support\PdfAsset::path('sys/right-arrow.png') }}"
                                                 style="width: 50px;height: 10px;">
                                            <p>
                                                @if(count($reservation->vol->escaliers->where('type',1))>1)
                                                    {{count($reservation->vol->escaliers->where('type',1))}} Escales
                                                @else
                                                    Direct
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            <p style="color: #fe6219">
                                                {{$reservation->vol->escaliers->where('type',1)->first()->airPortA->abv}}
                                            </p>
                                            <p>
                                                {{\Carbon\Carbon::parse($reservation->vol->heur_arriver_retour)->format("H:i")}}
                                            </p>
                                        </td>
                                        <td style="font-size: 20px">
                                            {{$reservation->vol->escaliers->where('type',1)->first()->model}}
                                        </td>

                                    </tr>
                                </table>
                                @if(count($reservation->vol->escaliers->where('type',1))>1)
                                    @foreach($reservation->vol->escaliers->where('type',1) as $escalier)
                                        <table>
                                            <tr>
                                                <td>
                                                    <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                                         style="height: 30px"
                                                         class="mr-3">
                                                    <p class="" style="font-size: 10px;">
                                                        {{$escalier->model}}
                                                    </p>
                                                </td>
                                                <td style="width: 70%">
                                                    <p style="color: #0b97c4;">
                                                        {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d m Y')}}
                                                    </p>
                                                    <table>
                                                        <tr>
                                                            <td>Départ</td>
                                                            <td style="">
                                                                <b>{{$escalier->heure_depart}}</b>
                                                            </td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortDe->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Arrivée</td>
                                                            <td><b> {{$escalier->heure_arriver}}</b></td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortA->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Temps vol</td>
                                                            <td><b> {{$escalier->temp}}</b></td>
                                                        </tr>

                                                    </table>

                                                </td>
                                            </tr>
                                        </table>
                                    @endforeach
                                @endif
                            </div>
                        </td>

                    </tr>
                </table>

            @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                <table>
                    <tr>
                        <td style="font-size: 16px;width: 150px">
                            <span style="color: #fe6219">Retour</span>
                            {{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('d/m/Y')}}
                        </td>
                        <td>
                            <div style="border: solid 2px #fe6219;width: 400px;padding: 7px 7px 7px 7px;">
                                <p>Presentation 3h avant depart</p>
                                <table>
                                    <tr>
                                        <td>
                                            <img src="{{ \App\Support\PdfAsset::path($reservation->volRetour->company->image) }}"
                                                 style="height: 30px;">
                                        </td>
                                        <td>
                                            <p style="color: #fe6219">
                                                {{$reservation->volRetour->escaliers->where('type',0)->first()->airPortDe->abv}}
                                            </p>
                                            <p>
                                                {{\Carbon\Carbon::parse($reservation->volRetour->heur_depart_aller)->format("H:i")}}
                                            </p>
                                        </td>
                                        <td>
                                            <p>
                                                {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                            </p>
                                            <img src="{{ \App\Support\PdfAsset::path('sys/right-arrow.png') }}"
                                                 style="width: 50px;height: 10px;">
                                            <p>
                                                @if(count($reservation->volRetour->escaliers->where('type',0))>1)
                                                    {{count($reservation->volRetour->escaliers->where('type',0))}}
                                                    Escales
                                                @else
                                                    Direct
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            <p style="color: #fe6219">
                                                {{$reservation->volRetour->escaliers->where('type',0)->first()->airPortA->abv}}
                                            </p>
                                            <p>
                                                {{\Carbon\Carbon::parse($reservation->volRetour->heur_arriver_aller)->format("H:i")}}
                                            </p>
                                        </td>
                                        <td style="font-size: 20px">
                                            {{$reservation->volRetour->escaliers->where('type',0)->first()->model}}
                                        </td>

                                    </tr>
                                </table>
                                <div style="text-align: center;">
                                    <p class="mt-3 ml-3" style="font-weight:700;font-size: 10px;color: #53CE7C">
                                        <img src="{{ \App\Support\PdfAsset::path('sys/luggage.png') }}" style="height: 10px"/>
                                        {{$reservation->volRetour->bagage}} Kgs soute +
                                        <img src="{{ \App\Support\PdfAsset::path('sys/back-pack.png') }}" style="height: 10px"/>
                                        {{$reservation->volRetour->bg_hand}} Kgs cabine
                                    </p>
                                </div>
                                @if(count($reservation->volRetour->escaliers->where('type',1))>1)
                                    @foreach($reservation->volRetour->escaliers->where('type',1) as $escalier)
                                        <table>
                                            <tr>
                                                <td>
                                                    <img src="{{ \App\Support\PdfAsset::path($reservation->volRetour->company->image) }}"
                                                         style="height: 30px"
                                                         class="mr-3">
                                                    <p class="" style="font-size: 10px;">
                                                        {{$escalier->model}}
                                                    </p>
                                                </td>
                                                <td style="width: 70%">
                                                    <p style="color: #0b97c4;">
                                                        {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d m Y')}}
                                                    </p>
                                                    <table>
                                                        <tr>
                                                            <td>Départ</td>
                                                            <td style="">
                                                                <b>{{$escalier->heure_depart}}</b>
                                                            </td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortDe->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Arrivée</td>
                                                            <td><b> {{$escalier->heure_arriver}}</b></td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortA->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Temps vol</td>
                                                            <td><b> {{$escalier->temp}}</b></td>
                                                        </tr>

                                                    </table>

                                                </td>
                                            </tr>
                                        </table>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            @elseif($reservation->type_voyage=='retour_simple')
                <table>
                    <tr>
                        <td style="font-size: 16px;width: 150px">
                            <p style="font-size: 10px;">
                                <span style="color: #fe6219;">Retour</span>
                                {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                            </p>
                        </td>
                        <td>
                            <div style="border: solid 2px #fe6219;width: 400px;padding: 7px 7px 7px 7px;">
                                <p>Presentation 3h avant depart</p>
                                <table>
                                    <tr>
                                        <td>
                                            <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                                 style="height: 30px;float: left">
                                        </td>
                                        <td>
                                            <p style="font-size: 10px;color: #fe6219">
                                                {{$reservation->vol->escaliers->where('type',0)->first()->airPortDe->abv}}
                                            </p>
                                            <p
                                                style="font-size: 10px;"> {{\Carbon\Carbon::parse($reservation->vol->heur_depart_aller)->format("H:i")}}</p>
                                        </td>
                                        <td>
                                            <p>
                                                {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                            </p>
                                            <img src="{{ \App\Support\PdfAsset::path('sys/right-arrow.png') }}"
                                                 style="width: 50px;height: 10px">
                                            <p>@if(count($reservation->vol->escaliers->where('type',0))>1)
                                                    {{count($reservation->vol->escaliers->where('type',0))}}
                                                    Escales
                                                @else
                                                    Direct
                                                @endif</p>
                                        </td>
                                        <td>
                                            <p style="font-size: 10px;color: #fe6219">
                                                {{$reservation->vol->escaliers->where('type',0)->last()->airPortA->abv}}
                                            </p>
                                            <p class="mb-0">
                                                {{\Carbon\Carbon::parse($reservation->vol->heur_arriver_aller)->format('H:i')}}
                                            </p>

                                        </td>
                                    </tr>
                                </table>
                                <div style="text-align: center;">
                                    <p class="mt-3 ml-3" style="font-weight:700;font-size: 10px;color: #53CE7C">
                                        <img src="{{ \App\Support\PdfAsset::path('sys/luggage.png') }}" style="height: 10px"/>
                                        {{$reservation->vol->bagage}} Kgs soute +
                                        <img src="{{ \App\Support\PdfAsset::path('sys/back-pack.png') }}" style="height: 10px"/>
                                        {{$reservation->vol->bg_hand}} Kgs cabine
                                    </p>
                                </div>
                                @if(count($reservation->vol->escaliers->where('type',0))>1)
                                    @foreach($reservation->vol->escaliers->where('type',0) as $escalier)
                                        <table>
                                            <tr>
                                                <td>
                                                    <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                                         style="height: 30px"
                                                         class="mr-3">
                                                    <p class="" style="font-size: 10px;">
                                                        {{$escalier->model}}
                                                    </p>
                                                </td>
                                                <td style="width: 70%">
                                                    <p style="color: #0b97c4;">
                                                        {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d m Y')}}
                                                    </p>
                                                    <table>
                                                        <tr>
                                                            <td>Départ</td>
                                                            <td style="">
                                                                <b>{{$escalier->heure_depart}}</b>
                                                            </td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortDe->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Arrivée</td>
                                                            <td><b> {{$escalier->heure_arriver}}</b></td>
                                                            <td style="color:#fe6219 ">{{$escalier->airPortA->abv}}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Temps vol</td>
                                                            <td><b> {{$escalier->temp}}</b></td>
                                                        </tr>

                                                    </table>

                                                </td>
                                            </tr>
                                        </table>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            @endif

        @endif
    </div>
</div>

<div class="section" style="clear: both;margin-top: 20px">
    <table style="width: 600px;margin-bottom: 10px">
        <tr>
            <td style="width: 200px;height: 30px;margin-bottom:0;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                LES BAGAGES
            </td>
            <td style="font-size: 12px;padding-left: 20px;font-weight: bold">
                {{$reservation->vol->bagage}} Kg soute + {{$reservation->vol->bg_hand}} Kg cabine
            </td>
        </tr>
    </table>

</div>
@if($reservation->vol->prix_comprend)
    <div class="section" style="clear: both;margin-top: 20px">
        <table style="width: 600px;margin-bottom: 10px">
            <tr>
                <td style="vertical-align: top;width: 200px;">
                    <p style="width: 200px;margin-bottom:0;padding-top:8px;padding-bottom:8px;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                        CE PRIX COMPREND
                    </p>
                </td>
                <td style="font-size: 12px;padding-left: 20px;">
                    {!! $reservation->vol->prix_comprend !!}
                </td>
            </tr>
        </table>
    </div>
@endif
@if($reservation->vol->conditions)
    <div class="section" style="clear: both;margin-top: 20px">
        <table style="width: 600px;margin-bottom: 10px">
            <tr>
                <td style="vertical-align: top;width: 200px;">
                    <p style="width: 200px;margin-bottom:0;padding-top:8px;padding-bottom:8px;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                        CE PRIX NE COMPREND PAS
                    </p>
                </td>
                <td style="font-size: 12px;padding-left: 20px;">
                    {!! $reservation->vol->prix_comprend_pas !!}
                </td>
            </tr>
        </table>
    </div>
@endif
@if($reservation->vol->conditions->Formalite_annulation)
    <div class="section" style="clear: both;margin-top: 20px">
        <table style="width: 600px;margin-bottom: 10px">
            <tr>
                <td style="vertical-align: top;width: 200px;">
                    <p style="width: 200px;margin-bottom:0;padding-top:8px;padding-bottom:8px;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                        CONDITIONS D'ANNULATION </p>
                </td>
                <td style="font-size: 12px;padding-left: 20px;">
                    {!! $reservation->vol->conditions->Formalite_annulation !!}
                </td>
            </tr>
        </table>
    </div>
@endif

<div class="section" style="clear: both;margin-top: 20px">
    <table style="width: 600px;margin-bottom: 10px">
        <tr>
            <td style="vertical-align: top;width: 200px;">
                <p style="width: 200px;margin-bottom:0;padding-top:8px;padding-bottom:8px;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                    CONDITIONS DU BILLET
                </p>
            </td>
            <td style="font-size: 12px;padding-left: 20px;font-weight: bold">
                BILLET NON MODIFIABLE ET NON REMBOURSABLE
            </td>
        </tr>
    </table>
</div>
<div class="section" style="clear: both;margin-top: 20px">
    <table style="width: 600px;margin-bottom: 10px">
        <tr>
            <td style="vertical-align: top;width: 200px;">
                <p style="width: 200px;margin-bottom:0;padding-top:8px;padding-bottom:8px;background:rgb(242, 64, 15);color: white;font-weight: bold;text-align: center;border-radius: 8px 8px 8px 8px; ">
                    Assistance
                </p>
            </td>
            <td style="font-size: 12px;padding-left: 20px;font-weight: bold">
                <p>Responsable Assistance aéroport Mr MOEZ 0635474382</p>
                <p>N telephone volotea 00 34 93 122 62 34</p>
            </td>
        </tr>
    </table>
</div>


</body>
</html>

