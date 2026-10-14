@extends('layouts.admin')

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('styles')
    <style>
        td, th {
            text-align: start;
            white-space: nowrap;
            padding-top: 15px !important;
            padding-bottom: 15px !important;

        }

        .plus, .moins, .CalculDevisButton {
            cursor: pointer;
        }

    </style>

@endsection

@section('content')

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Devis
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Devis</li>
            </ol>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body table-responsive">
                        <table class="table table-flush double-scroll">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">Ville depart</th>
                                <th scope="col">Ville arrivée</th>
                                <th scope="col">Date depart</th>
                                <th scope="col">Date retour</th>
                                <th scope="col">Nb passagers</th>
                                <th scope="col">Nom complet</th>
                                <th scope="col">Telephone</th>
                                <th scope="col">Email</th>
                                <th scope="col">Date de devis</th>
                            </tr>
                            </thead>
                            <tbody class="table_content">
                            @foreach($devises as $devis)
                                <tr>
                                    <td >
                                        {{$devis->departure_city}}
                                    </td>
                                    <td >
                                        {{$devis->arrival_city}}
                                    </td>
                                    <td >
                                        {{$devis->departure_date}}
                                    </td>
                                    <td >
                                        {{$devis->return_date}}
                                    </td>
                                    <td >
                                        {{$devis->passengers_nb}}
                                    </td>
                                    <td >
                                        {{$devis->first_name}} {{$devis->last_name}}
                                    </td>
                                    <td >
                                        {{$devis->phone_num}}
                                    </td>
                                    <td >
                                        {{$devis->email}}
                                    </td>
                                    <td >
                                        {{\Carbon\Carbon::parse($devis->created_at)->format('d/m/Y H:i')}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="devisCalculateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                        <img src="{{asset('front/img/plus.png')}}" inputTarget="enfantcountinput" style="width: 20px"
                             class="plus mr-4 calculateDevis" typeCalcul="plus" spanTarget="enfantcount">
                        <span style="font-size: 17px">Enfant (> 2 ans)</span>
                    </p>
                    <p class="d-flex align-items-center  mt-3" style="width: 350px" type="bebe">
                        <img src="{{asset('front/img/moins.png')}}" inputTarget="bebecountinput"
                             class="moins calculateDevis mr-4" style="width: 20px"
                             typeCalcul="mois" spanTarget="bebecountinput">
                        <span id="bebecount" style="font-size: 18px" class="nbtext mr-4 counter">0</span>
                        <img src="{{asset('front/img/plus.png')}}" inputTarget="bebecountinput" style="width: 20px"
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

    <script src="{{ asset('assets/vendor/double-scroll/doubleScroll.js') }}"></script>

    <script>
        $(document).ready(function () {
            // dif vols inscription section
            $('#reset').click(function () {
                $('input[name="vol_id"]').prop('checked', false);
                $('input[name="vol_id_retour"]').prop('checked', false);
                $('.dif_vols_inscription_buttons_section').addClass('d-none')

            })

            $('input[name="vol_id"], input[name="vol_id_retour"]').on('change', function () {
                $('.dif_vols_inscription_buttons_section').removeClass('d-none')
                // Do something else here
            });

            /////////// sort table

            $(document).on('mouseenter', '.vol_row', function () {
                $(this).children('td').addClass("table-primary")
            })
            $(document).on('mouseleave', '.vol_row', function () {
                $(this).children('td').removeClass("table-primary")
            })

})

        //////////////Calculate devis
        $clickedRow = 0
        $(document).on('click', '.CalculDevisButton', function () {
            $clickedRow = $(this).attr('vol_id')

            $('.counter').text(0)
            $('.counterInput').val(0)
            $('#devisCalculateModal').modal('show')
        })
        $('.calculateDevis').click(function () {
            $inputTarget = 'vol' + $clickedRow + $(this).attr('inputTarget')
            console.log($inputTarget)
            $spanTarget = $(this).attr('spanTarget')
            $type = $(this).attr('typeCalcul')
            $number = parseInt($('#' + $inputTarget).val())
            console.log('number ' + $number)

            if ($type == 'mois') {
                if ($number > 0) {
                    $number--;
                }
            } else {
                $number++;
            }
            $('#' + $inputTarget).val($number);
            $('#' + $spanTarget).text($number);
            $adultcountinput = parseInt($('#vol' + $clickedRow + 'adultcountinput').val())
            $enfantcountinput = parseInt($('#vol' + $clickedRow + 'enfantcountinput').val())
            $bebecountinput = parseInt($('#vol' + $clickedRow + 'bebecountinput').val())
            console.log($enfantcountinput)
            console.log($bebecountinput)

            $prixTotal = ($('#vol_num_' + $clickedRow).attr('priceadult') * ($adultcountinput + $enfantcountinput)) + ($('#vol_num_' + $clickedRow).attr('pricebebe') * $bebecountinput)
            $('#vol_num_' + $clickedRow).text($adultcountinput + ' ADLT +' + $enfantcountinput + ' ENFT +' + $bebecountinput + ' BB =' + $prixTotal + ' €');

// alert($number)

})
        $('.double-scroll').doubleScroll();

</script>
@endpush
