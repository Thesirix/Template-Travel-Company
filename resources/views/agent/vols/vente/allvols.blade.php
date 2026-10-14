@extends('layouts.admin')
    @section('SideBar')
        <x-side-bar/>
    @endsection

    @section('TopBar')
        @include('agent.partials.top-nav')
    @endsection

    @section('styles')
        <link rel="stylesheet" href="{{asset('assets/vendor/select2/dist/css/select2.min.css')}}">
        <style>
            td, th { white-space: nowrap; }
            .tc-rank td { vertical-align: top; }
            .tc-rank tfoot td { font-weight: 600; border-top: 1px solid var(--tc-border-strong); background: var(--tc-surface-2); }
            .tc-rank__pos { width: 20px; color: var(--tc-muted); font-variant-numeric: tabular-nums; }
            .tc-rank__bar { height: 4px; border-radius: 99px; background: var(--tc-hover); margin-top: 6px; overflow: hidden; max-width: 220px; }
            .tc-rank__bar i { display: block; height: 100%; background: var(--tc-primary); border-radius: inherit; }
            .tc-fillcity + .tc-fillcity { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--tc-border); }
            .tc-fillcity__head { display: flex; justify-content: space-between; margin-bottom: 8px; }
            .tc-fillcity__head span { color: var(--tc-muted); font-size: .8rem; }
            .tc-fillcity__row { display: flex; align-items: center; gap: 10px; font-size: .8rem; margin-top: 6px; }
            .tc-fillcity__row .tc-rank__bar { max-width: none; }
            .tc-fillcity__label { width: 48px; color: var(--tc-muted); }
            .tc-fillcity__value { width: 64px; text-align: right; font-variant-numeric: tabular-nums; }
            .tc-fillcity__rate { width: 52px; white-space: nowrap; text-align: right; color: var(--tc-muted); font-variant-numeric: tabular-nums; }
            .CalculDevisButton { color: var(--tc-primary); font-weight: 500; }
            .table th .fa-sort { font-size: .7rem; margin-left: 6px; }
            .table th .d-flex { justify-content: flex-start !important; }

            .plus, .moins, .CalculDevisButton {
                cursor: pointer;
            }

        </style>

    @endsection

    @section('content')

        <div class="container-fluid" id="container-wrapper">
            <div class="tc-pagehead">
                <div>
                    <h1>{{ $flightGroup->name }}</h1>
                    <p>{{ $flightGroup->country->nom }} · {{ $flightGroup->flights->count() }} vol{{ $flightGroup->flights->count() > 1 ? 's' : '' }} · {{ $flightGroup->flights->sum('place_reserver') }} places vendues</p>
                </div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('agent.home') }}">Accueil</a></li>
                    <li class="breadcrumb-item">{{ $flightGroup->country->nom }}</li>
                    <li class="breadcrumb-item active">{{ $flightGroup->name }}</li>
                </ol>
            </div>

            <div class="row">
                @if(! empty($agentsStatistics))
                    <div class="col-xl-4 mb-4"><x-agent.vols.vente.allvols.best-sellers-table :agentsStatistics="$agentsStatistics"/></div>
                @endif
                @if(! empty($agenciesStatistics))
                    <div class="col-xl-4 mb-4"><x-agent.vols.vente.allvols.best-agencies-table :$agenciesStatistics/></div>
                @endif
                @if(! empty($destinationsStatistics))
                    <div class="col-xl-4 mb-4"><x-agent.vols.vente.allvols.best-destinations-table :destinationsStatistics="$destinationsStatistics"/></div>
                @endif
            </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="table-responsive">
                                @if($flightGroup->type == \App\Enums\GroupType::ROUND_TRIP->value)
                                    <table class="table table-flush double-scroll">
                                        <thead class="thead-light">
                                        <tr>
                                            <th scope="col">Itinéraire</th>
                                            <th scope="col" class="filter " order_by="date_aller" order_type="desc"
                                                order_vol_type="aller_retour" style="cursor: pointer">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Aller</span>
                                                    <i class="fa-solid fa-sort text-gray-400"></i>
                                                </div>
                                            </th>
                                            <th scope="col " class="filter" order_by="date_retour" order_type="desc"
                                                order_vol_type="aller_retour" style="cursor: pointer">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Retour</span>
                                                    <i class="fa-solid fa-sort text-gray-400"></i>
                                                </div>
                                            </th>
                                            
                                                <th scope="col" class="filter" order_by="villes.nom"
                                                    order_type="desc"
                                                    style="cursor: pointer">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>Destination</span>
                                                        <i class="fa-solid fa-sort text-gray-400"></i>
                                                    </div>
                                                </th>
                                            
                                            <th scope="col">Places</th>
                                            <th scope="col">Vendu</th>
                                            <th scope="col">Reste</th>
                                            
                                                <th scope="col">PNR</th>
                                            
                                            <th scope="col">Adulte</th>
                                            
                                                <th scope="col">Enfant</th>
                                            
                                            <th scope="col">Bébé</th>
                                            <th scope="col">Compagnie</th>
                                            <th scope="col">Devis</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table_content">
                                        @php
                                            $totalall=0;
                                        @endphp

                                        @foreach($flightGroup->flights as $flight)
                                            @php
                                                $totalall += $flight->place_reserver;
                                            @endphp
                                            <tr class="vol_row">
                                                <td class="font-weight-bold">
                                                    {{ $flight->itineraire }}
                                                </td>
                                                <td class="font-weight-bold">
                                                    {{\Carbon\Carbon::parse($flight->date_aller)->format('d/m/Y')}}
                                                </td>
                                                <td class="font-weight-bold">
                                                    {{\Carbon\Carbon::parse($flight->date_retour)->format('d/m/Y')}}
                                                </td>
                                                
                                                    <td class="font-weight-bold">
                                                        @isset($flight->ville)
                                                            {{$flight->ville->nom}}
                                                        @endisset
                                                    </td>
                                                
                                                <td>{{$flight->place_total}}</td>
                                                <td class="font-weight-bold">
                                                    @if($flight->place_total>$flight->place_reserver)
                                                        @if($flight->place_reserver<($flight->place_total*0.5))
                                                            <span
                                                                class="badge badge-danger">{{$flight->place_reserver}}</span>
                                                        @else
                                                            <span
                                                                class="badge badge-success">{{$flight->place_reserver}}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-secondary">Complet</span>
                                                    @endif
                                                </td>
                                                <td class="font-weight-bold">
                                                    {{$flight->place_disponible}}
                                                </td>
                                                
                                                    <td>{{$flight->pnr}}</td>
                                                
                                                @if(\Illuminate\Support\Facades\Auth::guard('gent')->user()->agence->is_partner_agency)
                                                    <td class="font-weight-bold">
                                                        {{floor($flight->prix_vente_adult)}}€
                                                    </td>
                                                    
                                                        <td class="font-weight-bold">
                                                            {{floor($flight->prix_vente_enfant)}}€
                                                        </td>
                                                    
                                                    <td class="font-weight-bold">
                                                        {{floor($flight->prix_vente_bebe)}}€
                                                    </td>
                                                @else
                                                    <td class="font-weight-bold" data-toggle="tooltip"
                                                        data-placement="right"
                                                        title="{{floor($flight->prix_achat_adult)}}€">
                                                        {{floor($flight->prix_vente_adult)}}€
                                                    </td>
                                                    
                                                        <td class="font-weight-bold"
                                                            data-toggle="tooltip"
                                                            data-placement="right"
                                                            title="{{floor($flight->prix_achat_enfant)}}€">{{floor($flight->prix_vente_enfant)}}
                                                            €
                                                        </td>
                                                    
                                                    <td class="font-weight-bold" data-toggle="tooltip"
                                                        data-placement="right"
                                                        title="{{floor($flight->prix_achat_bebe)}}€">{{floor($flight->prix_vente_bebe)}}
                                                        €
                                                    </td>
                                                @endif
                                                <td>
                                                    {{ $flight->company->nom ?? '—' }}
                                                </td>
                                                <td id="vol_num_{{$flight->id}}"
                                                    priceAdult="{{floor($flight->prix_vente_adult)}}"
                                                    priceEnfant="{{floor($flight->prix_vente_enfant)}}"
                                                    priceBebe="{{floor($flight->prix_vente_bebe)}}"
                                                    vol_id="{{$flight->id}}"
                                                    class="CalculDevisButton">
                                                    @if(\Illuminate\Support\Carbon::today() < $flight->date_aller && ($flight->place_disponible>0 || ($type_reservation == 'Reservation_noListeAttente' && $flight->place_disponible >= ($reservation->nb_adult + $reservation->nb_enfant))))
                                                        Calculer
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($flight->place_disponible>0 && \Illuminate\Support\Carbon::today() < \Illuminate\Support\Carbon::parse($flight->date_aller)->subDays($flight->days_before_closure))
                                                        @if($type_reservation == 'noReservation')
                                                            <form method="get"
                                                                  action="{{route('agent.vols.vente.create', $flightGroup->id)}}">
                                                                <input type="hidden" name="vol_id"
                                                                       value="{{$flight->id}}">
                                                                <input type="hidden" name="type_reservation"
                                                                       value="Reservation">
                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}adultcountinput"
                                                                       class="counterInput" name="adultcount"
                                                                       value="0">
                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}enfantcountinput"
                                                                       max="1" class="counterInput"
                                                                       name="enfantcount"
                                                                       value="0">
                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}bebecountinput"
                                                                       max="1"
                                                                       class="counterInput" name="bebecount"
                                                                       value="0">
                                                                <button class="btn btn-sm btn-primary">
                                                                    Inscription
                                                                </button>
                                                            </form>
                                                        @elseif($type_reservation == 'Reservation_noListeAttente' && $flight->place_disponible >= ($reservation->nb_adult + $reservation->nb_enfant))
                                                            <form method="get"
                                                                  action="{{route('agent.vols.reservations.change-flight.preview', $flightGroup->id)}}">
                                                                <input type="hidden" name="vol_id"
                                                                       value="{{$flight->id}}">
                                                                <input type="hidden" name="reservation_id"
                                                                       value="{{$reservation->id}}">
                                                                <input type="hidden" name="type_reservation"
                                                                       value="Reservation">
                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}adultcountinput"
                                                                       class="counterInput" name="adultcount"
                                                                       value="{{$reservation->nb_adult}}">

                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}enfantcountinput"
                                                                       max="1" class="counterInput"
                                                                       name="enfantcount"
                                                                       value="{{$reservation->nb_enfant}}">
                                                                <input type="hidden"
                                                                       id="vol{{$flight->id}}bebecountinput"
                                                                       max="1"
                                                                       class="counterInput" name="bebecount"
                                                                       value="{{$reservation->nb_bebe}}">

                                                                <button class="btn btn-sm btn-primary">
                                                                    Inscription
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    

                                @else
                                    <form method="get"
                                          @if($type_reservation == 'noReservation')
                                              action="{{route('agent.vols.vente.create', $flightGroup->id)}}"
                                          @elseif($type_reservation == 'Reservation_noListeAttente')
                                              action="{{route('agent.vols.reservations.change-flight.preview', $flightGroup->id)}}"
                                        @endif
                                    >
                                        @if($type_reservation == 'noReservation')
                                            <input type="hidden" name="type_reservation"
                                                   value="Reservation">
                                            <input type="hidden"
                                                   class="counterInput" name="adultcount"
                                                   value="0">
                                            <input type="hidden"
                                                   max="1" class="counterInput" name="enfantcount"
                                                   value="0">
                                            <input type="hidden" max="1"
                                                   class="counterInput" name="bebecount"
                                                   value="0">
                                        @elseif($type_reservation == 'Reservation_noListeAttente')
                                            <input type="hidden" name="reservation_id"
                                                   value="{{$reservation->id}}">
                                            <input type="hidden" name="type_reservation"
                                                   value="Reservation">

                                            <input type="hidden"
                                                   class="counterInput" name="adultcount"
                                                   value="{{$reservation->nb_adult}}">
                                            <input type="hidden"
                                                   class="counterInput" name="enfantcount"
                                                   value="{{$reservation->nb_enfant}}">
                                            <input type="hidden" max="1"
                                                   class="counterInput" name="bebecount"
                                                   value="{{$reservation->nb_bebe}}">
                                        @endif
                                        <div class="text-center mb-3">
                                            <button class="btn btn-primary dif_vols_inscription_button d-none">
                                                Inscription
                                            </button>
                                            <button type="button" class="btn btn-danger" id="reset">
                                                Réinitialiser
                                            </button>
                                        </div>
                                        <div class="d-flex">
                                            <div class="mr-3"
                                                 style="overflow-x: scroll;overflow-y: scroll;height: 80vh">
                                                <h2 class="h6 mb-3">Aller</h2>
                                                @php
                                                    $totalaller=0;
                                                @endphp
                                                <table class="table table-bordered table-flush double-scroll ">
                                                    <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Itinéraire</th>
                                                        <th scope="col" class="filter " order_by="date_aller"
                                                            order_type="desc" order_vol_type="aller_simple"
                                                            style="cursor: pointer">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <span>Date</span>
                                                                <i class="fa-solid fa-sort text-gray-400"></i>
                                                            </div>
                                                        </th>
                                                        <th scope="col" style="cursor: pointer">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <span>Horaires</span>
                                                                <i class="fa-solid fa-sort text-gray-400"></i>
                                                            </div>
                                                        </th>
                                                        <th scope="col">Places</th>
                                                        <th scope="col">Vendu</th>
                                                        <th scope="col">Reste</th>
                                                        
                                                            <th scope="col">PNR</th>
                                                        
                                                        <th scope="col">Adulte</th>
                                                        <th scope="col">Enfant</th>
                                                        <th scope="col">Bébé</th>
                                                        <th scope="col">Compagnie</th>
                                                        <th scope="col">Clôture (j)</th>
                                                        <th scope="col">Choisir</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="table_content_aller">
                                                    @foreach($flightGroup->flights->where('type_voyage',\App\Enums\FlightType::OUTBOUND->value) as $flight)
                                                        <tr class="vol_row">
                                                            @php
                                                                $totalaller += $flight->place_reserver;
                                                            @endphp
                                                            <td class="font-weight-bold">
                                                                {{$flight->itineraire}}
                                                            </td>
                                                            <td class="font-weight-bold">
                                                                {{\Carbon\Carbon::parse($flight->date_aller)->format('d/m/Y')}}
                                                            </td>
                                                            <td>{{ \Illuminate\Support\Carbon::parse($flight->heur_depart_aller)->format('H:i')}}
                                                                -> {{ \Illuminate\Support\Carbon::parse($flight->heur_arriver_aller)->format('H:i')}}</td>
                                                            <td>{{$flight->place_total}}</td>
                                                            <td class="font-weight-bold">
                                                                @if($flight->place_total>$flight->place_reserver)
                                                                    @if($flight->place_reserver<($flight->place_total*0.5))
                                                                        <span
                                                                            class="badge badge-danger">{{$flight->place_reserver}}</span>
                                                                    @else
                                                                        <span
                                                                            class="badge badge-success">{{$flight->place_reserver}}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-secondary">Complet</span>
                                                                @endif
                                                            </td>
                                                            <td class="font-weight-bold">
                                                                {{$flight->place_disponible}}
                                                            </td>
                                                            
                                                                <td>{{$flight->pnr}}</td>
                                                            
                                                            @if(\Illuminate\Support\Facades\Auth::guard('gent')->user()->agence->is_partner_agency)
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_adult)}}€
                                                                </td>
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_enfant)}}€
                                                                </td>
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_bebe)}}€
                                                                </td>
                                                            @else
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_adult)}}€">{{floor($flight->prix_vente_adult)}}
                                                                    €
                                                                </td>
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_enfant)}}€">{{floor($flight->prix_vente_enfant)}}
                                                                    €
                                                                </td>
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_bebe)}}€">{{floor($flight->prix_vente_bebe)}}
                                                                    €
                                                                </td>
                                                            @endif
                                                            <td>
                                                                {{ $flight->company->nom ?? '—' }}
                                                            </td>
                                                            <td>{{$flight->days_before_closure}}</td>
                                                            <td>
                                                                @if(\Illuminate\Support\Carbon::today() < \Illuminate\Support\Carbon::parse($flight->date_aller)->subDays($flight->days_before_closure) && ((!isset($reservation) && $flight->place_disponible>0) || (isset($reservation) && $flight->place_disponible >= ($reservation->nb_adult + $reservation->nb_enfant))))
                                                                    <input type="radio" name="vol_id"
                                                                           itineraire="{{$flight->itineraire}}"
                                                                           value="{{$flight->id}}">
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                                <div class="small text-gray-500 mt-2">{{ $totalaller }} places vendues à l'aller</div>
                                            </div>
                                            <div style="overflow-x: scroll;overflow-y: scroll;height: 80vh">
                                                <h2 class="h6 mb-3">Retour</h2>
                                                @php
                                                    $totalretour=0;
                                                @endphp
                                                <table class="table table-bordered table-flush double-scroll">
                                                    <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Itinéraire</th>
                                                        <th scope="col" class="filter " order_by="date_aller"
                                                            order_type="desc" order_vol_type="retour_simple"
                                                            style="cursor: pointer">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <span>Date</span>
                                                                <i class="fa-solid fa-sort text-gray-400"></i>
                                                            </div>
                                                        </th>
                                                        <th scope="col " class="filter" style="cursor: pointer">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <span>Horaires</span>
                                                                <i class="fa-solid fa-sort text-gray-400"></i>
                                                            </div>
                                                        </th>
                                                        <th scope="col">Places</th>
                                                        <th scope="col">Vendu</th>
                                                        <th scope="col">Reste</th>
                                                        
                                                            <th scope="col">PNR</th>
                                                        
                                                        <th scope="col">Adulte</th>
                                                        <th scope="col">Enfant</th>
                                                        <th scope="col">Bébé</th>
                                                        <th scope="col">Compagnie</th>
                                                        <th scope="col">Clôture (j)</th>
                                                        <th scope="col">Choisir</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="table_content_retour">
                                                    @foreach($flightGroup->flights->where('type_voyage',\App\Enums\FlightType::RETURN->value) as $flight)
                                                        <tr class="vol_row">
                                                            @php
                                                                $totalretour += $flight->place_reserver;
                                                            @endphp
                                                            <td class="font-weight-bold">
                                                                {{$flight->itineraire}}
                                                            </td>
                                                            <td class="font-weight-bold">
                                                                {{\Carbon\Carbon::parse($flight->date_aller)->format('d/m/Y')}}
                                                            </td>
                                                            <td>{{ \Illuminate\Support\Carbon::parse($flight->heur_depart_aller)->format('H:i')}}
                                                                -> {{ \Illuminate\Support\Carbon::parse($flight->heur_arriver_aller)->format('H:i')}}</td>
                                                            <td>{{$flight->place_total}}</td>
                                                            <td class="font-weight-bold">
                                                                @if($flight->place_total>$flight->place_reserver)
                                                                    @if($flight->place_reserver<($flight->place_total*0.5))
                                                                        <span
                                                                            class="badge badge-danger">{{$flight->place_reserver}}</span>
                                                                    @else
                                                                        <span
                                                                            class="badge badge-success">{{$flight->place_reserver}}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-secondary">Complet</span>
                                                                @endif
                                                            </td>
                                                            <td class="font-weight-bold">
                                                                {{$flight->place_disponible}}
                                                            </td>
                                                            
                                                                <td>{{$flight->pnr}}</td>
                                                            
                                                            @if(\Illuminate\Support\Facades\Auth::guard('gent')->user()->agence->is_partner_agency)
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_adult)}}€
                                                                </td>
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_enfant)}}€
                                                                </td>
                                                                <td class="font-weight-bold">
                                                                    {{floor($flight->prix_vente_bebe)}}€
                                                                </td>
                                                            @else
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_adult)}}€">{{floor($flight->prix_vente_adult)}}
                                                                    €
                                                                </td>
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_enfant)}}€">{{floor($flight->prix_vente_enfant)}}
                                                                    €
                                                                </td>
                                                                <td class="font-weight-bold"
                                                                    data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    title="{{floor($flight->prix_achat_bebe)}}€">{{floor($flight->prix_vente_bebe)}}
                                                                    €
                                                                </td>
                                                            @endif
                                                            <td>
                                                                {{ $flight->company->nom ?? '—' }}
                                                            </td>
                                                            <td>{{$flight->days_before_closure}}</td>
                                                            <td>
                                                                @if(\Illuminate\Support\Carbon::today() < \Illuminate\Support\Carbon::parse($flight->date_aller)->subDays($flight->days_before_closure) && ((!isset($reservation) && $flight->place_disponible>0) || (isset($reservation) && $flight->place_disponible >= ($reservation->nb_adult + $reservation->nb_enfant))))
                                                                    <input type="radio" name="vol_id_retour"
                                                                           itineraire="{{$flight->itineraire}}"
                                                                           value="{{$flight->id}}">
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                                <div class="small text-gray-500 mt-2">{{ $totalretour }} places vendues au retour</div>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            
        </div>

        <div class="modal fade" id="devisCalculateModal" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Passagers</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex flex-column justify-content-center align-items-center">
                        
                        <p class="d-flex align-items-center  mt-5" style="width: 350px" type="adult">
                            <img src="{{asset('front/img/moins.png')}}" inputTarget="adultcountinput"
                                 typeCalcul="mois" spanTarget="adultcount"
                                 class="moins moins_adult mr-4 calculateDevis" style="width: 20px">
                            <span id="adultcount" style="font-size: 18px" class="nbtext mr-4 counter">0</span>
                            <img src="{{asset('front/img/plus.png')}}" inputTarget="adultcountinput"
                                 spanTarget="adultcount" typeCalcul="plus"
                                 class="plus mr-4 calculateDevis" style="width: 20px">
                            <span style="font-size: 17px">Adultes </span>
                        </p>
                        <p class="d-flex align-items-center  mt-3" style="width: 350px" type="enfant">
                            <img src="{{asset('front/img/moins.png')}}" inputTarget="enfantcountinput"
                                 class="moins calculateDevis mr-4" style="width: 20px"
                                 typeCalcul="mois" spanTarget="enfantcountinput">
                            <span id="enfantcount" style="font-size: 18px" class="nbtext mr-4 counter">0</span>
                            <img src="{{asset('front/img/plus.png')}}" inputTarget="enfantcountinput"
                                 style="width: 20px"
                                 class="plus mr-4 calculateDevis" typeCalcul="plus" spanTarget="enfantcount">
                            <span style="font-size: 17px">Enfant (> 2 ans)</span>
                        </p>
                        <p class="d-flex align-items-center  mt-3" style="width: 350px" type="bebe">
                            <img src="{{asset('front/img/moins.png')}}" inputTarget="bebecountinput"
                                 class="moins calculateDevis mr-4" style="width: 20px"
                                 typeCalcul="mois" spanTarget="bebecountinput">
                            <span id="bebecount" style="font-size: 18px" class="nbtext mr-4 counter">0</span>
                            <img src="{{asset('front/img/plus.png')}}" inputTarget="bebecountinput"
                                 style="width: 20px"
                                 class="plus mr-4 calculateDevis" typeCalcul="plus" spanTarget="bebecount">
                            <span style="font-size: 17px">Bebe (< 2 ans)</span>
                        </p>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @push("script")
        <script>
            $groupType = "{{$flightGroup->type}}";
            $isGroupsameItineraire = "{{$flightGroup->same_itineraire}}";
        </script>
        <script src="{{asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
        <script src="{{asset('assets/js/agent/vols/vente/allvols/tables.js?v3')}}"></script>

<script>
                /////////// sort table
                $(document).ready(function () {
                        function createInscriptionForm(volData) {
                            // Get the Blade variables directly into JavaScript
                            const volId = volData.id;
                            const placeDisponible = volData.place_disponible;
                            const dateAller = moment(volData.date_aller, 'DD/MM/YYYY').toDate();
                            const today = new Date();
                            const typeReservation = "{{ $type_reservation }}";
                            const flightGroupId = "{{ $flightGroup->id }}";
                            const nbAdult = {{ $reservation->nb_adult ?? 0 }};
                            const nbEnfant = {{ $reservation->nb_enfant ?? 0 }};
                            const nbBebe = {{ $reservation->nb_bebe ?? 0 }};
                            const reservationId = {{ $reservation->id ?? 'null' }};

                            // Only proceed if conditions are met
                            if (placeDisponible > 0 && today < dateAller) {
                                const form = document.createElement("form");
                                form.method = "get";
                                form.action = typeReservation === "noReservation"
                                    ? @json(route('agent.vols.vente.create', $flightGroup->id))
                                    : @json(route('agent.vols.reservations.change-flight.preview', $flightGroup->id));

                                // Hidden fields
                                const inputs = [
                                    {name: "vol_id", value: volId},
                                    {name: "type_reservation", value: "Reservation"},
                                    {
                                        name: "adultcount",
                                        value: typeReservation === "noReservation" ? "0" : nbAdult,
                                        id: `vol${volId}adultcountinput`,
                                        class: "counterInput"
                                    },
                                    {
                                        name: "enfantcount",
                                        value: typeReservation === "noReservation" ? "0" : nbEnfant,
                                        id: `vol${volId}enfantcountinput`,
                                        class: "counterInput",
                                        max: "1"
                                    },
                                    {
                                        name: "bebecount",
                                        value: typeReservation === "noReservation" ? "0" : nbBebe,
                                        id: `vol${volId}bebecountinput`,
                                        class: "counterInput",
                                        max: "1"
                                    }
                                ];

                                if (typeReservation !== "noReservation") {
                                    inputs.push({name: "reservation_id", value: reservationId});
                                }

                                // Append inputs to form
                                inputs.forEach(({name, value, id, class: className, max}) => {
                                    const input = document.createElement("input");
                                    input.type = "hidden";
                                    input.name = name;
                                    input.value = value;
                                    if (id) input.id = id;
                                    if (className) input.className = className;
                                    if (max) input.max = max;
                                    form.appendChild(input);
                                });

                                // Submit button
                                const button = document.createElement("button");
                                button.className = "btn btn-sm btn-primary";
                                button.textContent = "Inscription";
                                form.appendChild(button);

                                // Append form to the desired container
                                return form;
                            }
                        }

                        function createTableRow(volData, tableBodyName) {
                            const tableBody = document.getElementById(tableBodyName);
                            const row = document.createElement("tr");
                            row.className = "vol_row";
// Define the properties you want to display in the table
                            // Mêmes colonnes que le tableau affiché : aller-retour (table_content) ou aller / retour séparés
                            const isRoundTrip = tableBodyName === 'table_content';
                            let properties = ["itineraire", "date_aller"];
                            properties.push(...(isRoundTrip ? ["date_retour", "ville"] : ["horaire"]));
                            properties.push("place_total", "place_reserver", "place_disponible", "pnr");
                            properties.push("prix_vente_adult", "prix_vente_enfant", "prix_vente_bebe", "companyImage");
                            properties.push(...(isRoundTrip ? ["CalculDevisButton", "inscriptionForm"] : ["days_before_closure", "radio"]));

                            let pricesCells = ['prix_vente_adult', 'prix_vente_enfant', 'prix_vente_bebe']
                            // Create table cells and set their content dynamically
                            const cells = properties.map((property) => {
                                const cell = document.createElement("td");
                                switch (property) {
                                    case 'date_retour':
                                    case 'date_aller':
                                        cell.style.fontWeight = 700;
                                        cell.style.fontSize = '20px';
                                        cell.textContent = volData[property];
                                        break;

                                    case 'place_reserver': {
                                        if (volData['place_reserver'] == volData['place_total']) {
                                            // Create the image element
                                            const img = document.createElement('img');
                                            // Set the image source
                                            img.src = '{{asset('sys/tout_vendu.jpeg')}}';
                                            // Set the style width
                                            img.style.width = '70px';
                                            // Append the image to the desired container
                                            cell.append(img);
                                        } else {
                                            const span = document.createElement("span");
                                            span.className = volData['place_reserver'] < (volData['place_total'] * 0.5) ? 'badge badge-danger' : 'badge badge-success';
                                            span.textContent = volData[property];
                                            cell.append(span);
                                        }
                                        break;
                                    }

                                    case 'companyImage': {
                                        const image = document.createElement("img");
                                        image.style.width = '130px';
                                        image.src = volData['company']['image'];
                                        cell.append(image);
                                        break;
                                    }
                                    case 'itineraire': {
                                        cell.style.fontWeight = 700;
                                        cell.style.color = volData['itineraire_color'];
                                        cell.textContent = volData[property];
                                        break;
                                    }
                                    case 'CalculDevisButton': {
                                        cell.textContent = 'Calculer';
                                        cell.setAttribute("id", `vol_num_${volData.id}`);
                                        cell.setAttribute("priceAdult", Math.floor(volData.prix_vente_adult));
                                        cell.setAttribute("priceEnfant", Math.floor(volData.prix_vente_enfant));
                                        cell.setAttribute("priceBebe", Math.floor(volData.prix_vente_bebe));
                                        cell.setAttribute("vol_id", volData.id);
                                        cell.classList.add("CalculDevisButton");
                                        break;
                                    }

                                    case 'radio': {
                                        if (moment(volData.date_aller, 'DD/MM/YYYY').toDate() > moment()) {
                                            const radio = document.createElement("input");
                                            radio.type = 'radio';
                                            radio.name = tableBodyName === 'table_content_aller' ? 'vol_id' : 'vol_id_retour';
                                            radio.value = volData['id'];
                                            radio.setAttribute("itineraire", volData.itineraire);
                                            cell.append(radio);
                                        } else {
                                            cell.append('');
                                        }
                                        break;
                                    }
                                    case 'inscriptionForm': {
                                        const inscriptionForm = createInscriptionForm(volData)
                                        cell.append(inscriptionForm ? inscriptionForm : '');
                                        break;
                                    }

                                    default:
                                        if (pricesCells.includes(property)) {
                                            cell.textContent = volData[property] + '€';
                                            cell.style.fontSize = '17px';
                                            cell.style.fontWeight = 700;
                                            cell.setAttribute('data-toggle', 'tooltip');
                                            cell.setAttribute('data-placement', 'right');
                                            cell.setAttribute('title', property.replace('ventre', 'achat'));
                                        } else {
                                            cell.textContent = volData[property];
                                        }
                                }

                                return cell;
                            });

// Add all cells to the row
                            cells.forEach((cell) => {
                                row.appendChild(cell);
                            });

// Add the row to the table body
                            tableBody.appendChild(row);
                        }

                        function createDataObject(data) {
                            const volData = {
                                itineraire: data.itineraire,
                                itineraire_color: data.itineraire_color,
                                date_aller: moment(data.date_aller).format('DD/MM/YYYY'),
                                date_retour: moment(data.date_retour).format('DD/MM/YYYY'),
                                ville: data.ville ? data.ville.nom : '',
                                days_before_closure: data.days_before_closure,
                                horaire: moment(data.heur_depart_aller, "HH:mm:ss").format('HH:mm') + ' -> ' + moment(data.heur_arriver_aller, "HH:mm:ss").format('HH:mm'),
                                place_total: data.place_total,
                                place_reserver: data.place_reserver,
                                place_disponible: data.place_disponible,
                                pnr: data.pnr,
                                prix_vente_adult: data.prix_vente_adult,
                                prix_vente_enfant: data.prix_vente_enfant,
                                prix_vente_bebe: data.prix_vente_bebe,
                                prix_achat_adult: data.prix_vente_adult,
                                prix_achat_enfant: data.prix_vente_enfant,
                                prix_achat_bebe: data.prix_vente_bebe,
                                company: {
                                    image: data.company.image,
                                },
                                id: data.id,
                            };
                            return volData;
                        }

                        $('.filter').click(function () {
                            let groupType = '{{$flightGroup->type}}';
                            if (groupType != '{{\App\Enums\GroupType::ROUND_TRIP->value}}') {
                                $('input[name="vol_id"]').prop('checked', false);
                                $('input[name="vol_id_retour"]').prop('checked', false);
                                $('input[name="vol_id"]').show();
                                $('input[name="vol_id_retour"]').show();
                                $('.dif_vols_inscription_button').addClass('d-none');
                                outboundFlightChecked = false;
                                returnFlightChecked = false;
                            }
                            let ordertype = $(this).attr('order_type');
                            let order_by = $(this).attr('order_by')
                            let order_vol_type = $(this).attr('order_vol_type')

                            if (ordertype == "desc") {
                                $(this).attr('order_type', 'asc')
                            } else {
                                $(this).attr('order_type', 'desc')
                            }
                            $.ajax({
                                type: 'get',
                                url: '{{ route('agent.vols.vente.filter', $flightGroup->id) }}',
                                data: {
                                    'order_type': ordertype,
                                    'order_by': order_by,
                                    'order_vol_type': order_vol_type
                                },
                                success: function (data) {
                                    console.log(data);
                                    if (groupType != '{{\App\Enums\GroupType::ROUND_TRIP->value}}') {
                                        if (order_vol_type == 'aller_simple') {
                                            $('#table_content_aller').html('')
                                            for (let i = 0; i < data.length; i++) {
                                                volData = createDataObject(data[i])
                                                createTableRow(volData, 'table_content_aller')
                                            }
                                        } else if (order_vol_type == 'retour_simple') {
                                            $('#table_content_retour').html('')
                                            for (let i = 0; i < data.length; i++) {
                                                volData = createDataObject(data[i])
                                                createTableRow(volData, 'table_content_retour')
                                            }
                                        }
                                    } else {
                                        $('#table_content').html('');
                                        for (let i = 0; i < data.length; i++) {
                                            volData = createDataObject(data[i])
                                            createTableRow(volData, 'table_content')
                                        }
                                    }
                                }
                                , error: function (e) {
                                    console.log(e)
                                }
                            });

                        })
                    }
                )
            </script>

@endpush
