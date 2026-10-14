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
            @if(count($reservation->vol->escaliers) < 2)
                            font-size: 12px;
            @else
                font-size: 8px;
        @endif

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

        .section-title {
            width: 150px;
            height: 15px;
            text-align: center;
            padding-top: 7px;
            background: rgb(242, 64, 15);
            color: white;
            font-weight: bold;
            border-radius: 2px 2px 2px 2px;
        }

        .w-160 {
            width: 160px;
        }

        .contact-section .contact-item {
            padding-bottom: 8px;
            padding-top: 8px;
            text-align: center;
            color: white;
            background: rgb(242, 64, 15);
            font-weight: bold;
        }

        .contact-section .contact-item p {
            font-size: 9px;

        }

.transport-section .transport-section-title {
            font-size: 12px;
        }

        .transport-section .company-logo {
            height: 30px;
            float: left;
        }

        .transport-section .text {
            font-size: 10px;
        }

    </style>
</head>
<body>
<table class="header" style="text-align: center">
    <tr>
        <td>
            <img src="{{ \App\Support\PdfAsset::path($reservation->gent->agence->image) }}" style="width: 120px;">
        </td>
        <td>
            <p style="font-size: 18px">Contrat de vente / Transport seul</p>
        </td>

        <td>
            
        </td>
    </tr>
</table>
<div class="section" style="clear: both" style="display: flex">
    <div class="section-title">
        RESERVATION
    </div>
    <p class="titre">Nombre de participants au voyage : {{$reservation->passagers->count()}}</p>
    <table class="table passager-table">
        <tr>
            <th>Titre</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Catégorie</th>
            <th>Prix TTC</th>
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
                <td>
                    
                        @if($person->type=="adulte")
                            {{floor($reservation->vol->prix_vente_adult)}} € +
                            @if($reservation->type_voyage=='aller_retour_diffrent_vol')
                                {{floor($reservation->volRetour->prix_vente_adult)}} €
                            @endif
                        @elseif($person->type=="enfant")
                            {{floor($reservation->vol->prix_vente_enfant)}} € +
                            @if($reservation->type_voyage=='aller_retour_diffrent_vol')
                                {{floor($reservation->volRetour->prix_vente_enfant)}} €
                            @endif
                        @else
                            {{floor($reservation->vol->prix_vente_bebe)}} € +
                            @if($reservation->type_voyage=='aller_retour_diffrent_vol')
                                {{floor($reservation->volRetour->prix_vente_bebe)}} €
                            @endif
                        @endif
                    
                </td>

            </tr>
        @endforeach

<tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Total</td>
            <td>{{floor($reservation->prix_total)}}€</td>

        </tr>

    </table>
</div>

<div class="section" style="clear: both">
    <table>
        <tr>
            <td class="w-160">
                <div class="section-title">
                    TRANSPORT
                </div>
            </td>
            <td>
                @if($reservation->type_voyage != 'retour_simple')
                    <div class="transport-section"
                         style="border: solid 2px #fe6219;width: 47%;padding: 7px 7px 7px 7px;@if(count($reservation->vol->escaliers) >= 2)float: left @endif">
                        <div style="text-align: center;width: 100%;">
                            <p class="transport-section-title">
                                <span style="color: #fe6219;">Aller</span>
                                {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                            </p>
                        </div>
                        <table>
                            <tr>
                                <td>
                                    <img class="company-logo" src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                         style="float: left">
                                </td>
                                <td>
                                    <p class="text" style="color: #fe6219">
                                        {{$reservation->vol->escaliers->where('type',0)->first()->airPortDe->abv}}
                                    </p>
                                    <p class="text"> {{\Carbon\Carbon::parse($reservation->vol->heur_depart_aller)->format("H:i")}}</p>
                                </td>
                                <td>
                                    <p>
                                        {{$durationAller->format('H').' h '.$durationAller->format('i').' min'}}
                                    </p>
                                    <img src="{{ \App\Support\PdfAsset::path('sys/right-arrow.png') }}" style="width: 50px;height: 10px">
                                    <p>@if(count($reservation->vol->escaliers->where('type',0))>1)
                                            {{count($reservation->vol->escaliers->where('type',0))}}
                                            Escales
                                        @else
                                            Direct
                                        @endif</p>
                                </td>
                                <td>
                                    <p class="text" style="color: #fe6219">
                                        {{$reservation->vol->escaliers->where('type',0)->last()->airPortA->abv}}
                                    </p>
                                    <p class="text" class="mb-0">
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
                @endif
                @if($reservation->type_voyage != 'aller_simple')
                    
                    @if($reservation->type_voyage=='aller_retour_same_vol')
                        <div class="transport-section"
                             style="border: solid 2px #fe6219;width: 47%;padding: 7px 7px 7px 7px;float: left;">
                            <div>
                                <p class="transport-section-title">
                                    <span style="color: #fe6219">Retour</span>
                                    {{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}
                                </p>
                            </div>
                            <table>
                                <tr>
                                    <td>
                                        <img class="company-logo" src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}">
                                    </td>
                                    <td>
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',1)->first()->airPortDe->abv}}
                                        </p>
                                        <p class="text">
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
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',1)->first()->airPortA->abv}}
                                        </p>
                                        <p class="text">
                                            {{\Carbon\Carbon::parse($reservation->vol->heur_arriver_retour)->format("H:i")}}
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
                            @if(count($reservation->vol->escaliers->where('type',1))>1)
                                @foreach($reservation->vol->escaliers->where('type',1) as $escalier)
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

                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                        <div class="transport-section"
                             style="border: solid 2px #fe6219;width: 47%;padding: 7px 7px 7px 7px;float: left">
                            <div style="text-align: center;width: 100%;">
                                <p class="transport-section-title">
                                    <span style="color: #fe6219">Retour</span>
                                    {{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('d/m/Y')}}
                                </p>
                            </div>
                            <table>
                                <tr>
                                    <td>
                                        <img src="{{ \App\Support\PdfAsset::path($reservation->volRetour->company->image) }}"
                                             class="company-logo">
                                    </td>
                                    <td>
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->volRetour->escaliers->where('type',0)->first()->airPortDe->abv}}
                                        </p>
                                        <p class="text">
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
                                                {{count($reservation->volRetour->escaliers->where('type',0))}} Escales
                                            @else
                                                Direct
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->volRetour->escaliers->where('type',0)->first()->airPortA->abv}}
                                        </p>
                                        <p class="text">
                                            {{\Carbon\Carbon::parse($reservation->volRetour->heur_arriver_aller)->format("H:i")}}
                                        </p>
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
                    @elseif($reservation->type_voyage=='retour_simple')
                        <div class="transport-section"
                             style="border: solid 2px #fe6219;width: 47%;padding: 7px 7px 7px 7px;float: left">
                            <div style="text-align: center;width: 100%;">
                                <p class="transport-section-title">
                                    <span style="color: #fe6219;">Retour</span>
                                    {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                </p>
                            </div>
                            <table>
                                <tr>
                                    <td>
                                        <img src="{{ \App\Support\PdfAsset::path($reservation->vol->company->image) }}"
                                             class="company-logo">
                                    </td>
                                    <td>
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',0)->first()->airPortDe->abv}}
                                        </p>
                                        <p class="text"> {{\Carbon\Carbon::parse($reservation->vol->heur_depart_aller)->format("H:i")}}</p>
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
                                        <p class="text" style="color: #fe6219">
                                            {{$reservation->vol->escaliers->where('type',0)->last()->airPortA->abv}}
                                        </p>
                                        <p class="text" class="mb-0">
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
                    @endif

                @endif

            </td>
        </tr>
    </table>
    <div>
    </div>

</div>

<div class="section" style="clear: both">
    <table>
        <tr>
            <td class="w-160">
                <div class="section-title">
                    LES BAGAGES
                </div>
            </td>
            <td>
                <p style="font-weight: 700;">
                    {{$reservation->vol->bagage}} Kg soute + {{$reservation->vol->bg_hand}} Kg cabine
                </p>
            </td>
        </tr>
    </table>
</div>
@if($reservation->vol->prix_comprend)
    <div class="section">

        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CE PRIX COMPREND
                    </div>
                </td>
                <td>
                    <p style="display: flex;flex-direction: row;flex-wrap: nowrap;padding-left: 0">
                        {!! $reservation->vol->prix_comprend !!}
                    </p>
                </td>
            </tr>
        </table>
    </div>
@endif
@if($reservation->vol->conditions)
    <div class="section" style="clear: both">
        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CONDITION SANTE
                    </div>
                </td>
                <td>
                    {!! $reservation->vol->conditions->Formalite_sante !!}
                </td>
            </tr>
        </table>
    </div>
    <div class="section">
        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CONDITION POLICE
                    </div>
                </td>
                <td>
                    {!! $reservation->vol->conditions->Formalite_police!!}
                </td>
            </tr>
        </table>
    </div>
    <div class="section">
        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CONDITIONS D'INSCRIPTION
                    </div>
                </td>
                <td>
                    {!! $reservation->vol->conditions->Formalite_inscription!!}
                </td>
            </tr>
        </table>
    </div>
    <div class="section">
        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CONDITIONS D'ANNULATION
                    </div>
                </td>
                <td>
                    {!!$reservation->vol->conditions->Formalite_annulation!!}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td class="w-160">
                    <div class="section-title">
                        CONDITIONS DU BILLET
                    </div>
                </td>
                <td>
                    <p style="font-weight: 700;">
                        BILLET MODIFIABLE ET REMBOURSABLE AVEC FRAIS
                    </p>
                </td>
            </tr>
        </table>
    </div>
@endif

<div class="section d-none" style="margin-left: 20px;margin-top: 10px">
    <div>
        <p>Fait le {{\Carbon\Carbon::now()->format('d/m/Y H:i')}}</p>
        <table>
            <tr>
                <td style="width: 48%">
                    <p><span
                            style="font-weight: 700">Le vendeur : </span> {{$reservation->gent->name .' '.$reservation->gent->prenom}}
                    </p>
                    <textarea style="height: 25px">Signature</textarea>
                </td>
                <td style="width: 4%"></td>
                <td style="width: 48%">
                    <p><span
                            style="font-weight: 700">Le Client : </span> {{$reservation->passagers->where('type','adulte')->first()->nom.' '.$reservation->passagers->where('type','adulte')->first()->prenom}}
                    </p>
                    <textarea style="height: 25px">Signature</textarea>
                </td>
            </tr>
        </table>
    </div>

</div>

@if(!$reservation->gent->agence->is_partner_agency)
    @php($offices = \App\Models\Agency::where('is_partner_agency', false)->orderBy('name')->get())
    <div class="section contact-section">
        <table>
            <tr>
                @foreach($offices as $office)
                    <td>
                        <div class="contact-item">
                            <p>{{ $brand->name() }}</p>
                            @if($office->rue)<p>{{ $office->rue }}</p>@endif
                            @if($office->ville)<p>{{ $office->ville }}</p>@endif
                            @if($office->phone)<p>{{ $office->phone }}</p>@endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
@endif
</body>
</html>

