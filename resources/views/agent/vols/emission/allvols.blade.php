@extends('layouts.admin')

@section("styles")
    <style>
        th, td {
            white-space: nowrap;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
    </style>

@endsection

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Emission</h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Emission</li>
            </ol>
        </div>
        <div class="card mb-4 mt-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Vols</h6>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="filter" order_by="date_aller" order_type="desc" style="cursor: pointer">
                            Aller
                        </th>
                        <th scope="col " class="filter" order_by="date_retour" order_type="desc"
                            style="cursor: pointer">
                            Retour
                        </th>
                        <th scope="col">Liste J-60</th>
                        <th scope="col">Solde J-45</th>
                        <th scope="col">Emission J-35</th>
                        <th scope="col">Seats</th>
                        <th scope="col">Vendu</th>
                        <th scope="col">Reste</th>
                        <th scope="col">PNR</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody class="table_content">
                    @foreach($flightsGroup->flights as $flight)
                        <tr class="vol_row">
                            <td style="font-size: 17px;font-weight: 700">
                                @if(in_array($flight->type_voyage,['aller_simple','aller_retour']))
                                    {{\Carbon\Carbon::parse($flight->date_aller)->format('d/m/Y')}}
                                @endif
                            </td>
                            <td style="font-size: 17px;font-weight: 700">
                                @if($flight->type_voyage=='retour_simple')
                                    {{\Carbon\Carbon::parse($flight->date_aller)->format('d/m/Y')}}
                                @elseif($flight->type_voyage=='aller_retour')
                                    {{\Carbon\Carbon::parse($flight->date_retour)->format('d/m/Y')}}
                                @endif
                            </td>

                            <td>
                                {{\Carbon\Carbon::parse($flight->date_aller)->subDays(60)->format('d-m-Y')}}
                            </td>
                            <td>
                                {{\Carbon\Carbon::parse($flight->date_aller)->subDays(45)->format('d-m-Y')}}
                            </td>

                            <td>
                                {{\Carbon\Carbon::parse($flight->date_aller)->subDays(35)->format('d-m-Y')}}
                            </td>

                            <td>{{$flight->place_total}}</td>
                            <td style="font-size: 17px;font-weight: 700">

{{--                                    --}}

                                {{$flight->place_reserver}}
                            </td>
                            <td style="font-size: 17px;font-weight: 700">
                                <p>{{$flight->place_disponible}}</p>

                            </td>
                            <td>{{$flight->pnr}}</td>
                            <td>
                                <a href="{{route('agent.vols.emission.flight', [$flightsGroup->id, $flight->id])}}"
                                   class="btn btn-sm btn-primary">
                                    Consultation
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push("script")
    <script>
        $(document).ready(function () {
            $(document).on('mouseenter', '.vol_row', function () {
                $(this).children('td').addClass("table-primary")
            })
            $(document).on('mouseleave', '.vol_row', function () {
                $(this).children('td').removeClass("table-primary")
            })

{{--    if (ordertype == "desc") {--}}
            
            {{--    } else {--}}

{{--    $.ajax({--}}
            {{--        type: 'get',--}}
            
            {{--        data: {--}}
            {{--            'order_type': ordertype,--}}
            {{--            'order_by': order_by--}}
            {{--        },--}}
            {{--        success: function (data) {--}}
            {{--            let html = ''--}}
            {{--            for (let i = 0; i < data.length; i++) {--}}

{{--        , error: function (e) {--}}

{{--})--}}

        })
    </script>

@endpush
