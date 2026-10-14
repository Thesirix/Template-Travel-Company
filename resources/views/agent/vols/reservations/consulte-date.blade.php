@extends('layouts.admin')

@section('SideBar')
    <x-side-bar />
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection
@section('styles')
    <style>
        #page1-validation-check1 {
            display: block !important;
        }

        .page-validation-check {
            display: none;
        }


        .error {
            color: red;
        }

        .plus, .moins {
            cursor: pointer;
        }

        table td {
            text-align: left !important;
        }

        .iti {
            position: relative;
            display: inline-block;
        }

        .iti__flag-container {
            position: absolute;
            top: 0;
            bottom: 0;
            padding: 1px;
        }

        .passportfield {
            padding-left: 52px;

        }

        .iti__selected-flag {
            z-index: 1;
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0 6px 0 8px;
        }


        .passportfieldfile {
            display: none;
        }

        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
        }


        .img_preview {
            cursor: pointer;
            position: relative;
            max-width: 200px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid mb-5" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Inscription @if($type_reservation=='liste_attente')
                    liste d'attente
                @endif
            </h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Recherche</li>
                <li class="breadcrumb-item">Consulte date</li>
            </ol>

        </div>


        <form method="post" class="submit-form"
              action="{{route('agent.vols.reservations.change-flight.apply', $flightsGroup->id)}}"
              enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="type_reservation" id="type_reservation" value="{{$type_reservation}}">
            <input type="hidden" name="type_voyage" id="type_voyage" value="{{$type_voyage}}">
            <input type="hidden" name="vol_id" value="{{$vol->id}}">
            @if($type_voyage == 'aller_retour_diffrent_vol')
                <input type="hidden" name="vol_retour_id" value="{{$vol_retour->id}}">
            @endif
            <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
            <input type="hidden" id="adultcountinput2" class="input" name="adultcountinput2" value="0">
            <input type="hidden" id="enfantcountinput2" class="input" name="enfantcountinput2" value="0">
            <input type="hidden" id="bebecountinput2" class="input" name="bebecountinput2" value="0">
            <input type="hidden" id="pricetoal_res" class="input" name="pricetoal_res">
            <input type="hidden" id="pagenum" value="1">

            
            
                <x-agent.vols.inscription.page1.index :$vol
                                                     :volRetour="$vol_retour"
                                                     :typeVoyage="$type_voyage"
                                                     :$durationAller
                                                     :$durationRetour
                                                     :$adultcount
                                                     :$enfantcount
                                                     :$bebecount
                                                     :$devisTotalPrice
                                                     :changeDate="true"/>
                <x-agent.vols.inscription.page2.index :$vol/>
                <x-agent.vols.inscription.page3.index :$vol/>
            
            <div class="container-fluid text-center " id="page4" style="margin-top:100px;display: none">
                <div class="">
                    <div class="d-flex justify-content-center">
                        <p style="color: #fe6219;font-size: 23px;width: 270px">Passagers</p>
                    </div>
                    <div class="d-flex flex-wrap" id="passagersinputs">
                        @foreach($reservation->passagers as $person)
                            <div class="mb-3 mx-3"
                                 style="border:solid 2px @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif;width: 400px"
                                 id="passager_{{$person->id}}">
                                <p class="text-light d-flex align-items-center justify-content-center px-4 py-3"
                                   style="font-size:20px;background-color: @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif ">
                                    {{$person->type}} @if($person->type=='bebe')
                                        < 2
                                        ans
                                    @elseif($person->type=='enfant')
                                        <
                                        12 ans
                                    @endif
                                </p>
                                <x-agent.vols.common.reservation-passager-card :$reservation
                                                                              :destination="strtolower($flightsGroup->country->nom)"
                                                                              :$person
                                                                              :$assurences
                                                                              :showVerificationSection="0"/>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex flex-wrap" id="passagersinputs">

                </div>
            </div>
            <div id="page5" style="margin-top:20vh;display: none">
                <div class="d-flex flex-wrap justify-content-center">
                    <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef;width: 600px">
                        <div class="card-header pb-3 pt-0">
                            <div style="height: 30px" class="bg-primary text-center">
                                <h6 class="m-0 font-weight-bold text-light">
                                    Recapitulatif
                                </h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <table style="font-size: 17px">
                                <tr>
                                    <td style="width: 30% " class="text-primary font-weight-bold pt-3">Itineraire</td>
                                    <td class="pt-3">
                                        {{$vol->itineraire}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pt-3 font-weight-bold text-primary">Compagnie</td>
                                    <td class="pt-3">{{$vol->company->nom}}</td>
                                </tr>
                                @if($type_voyage != 'retour_simple')
                                    <tr>
                                        <td class="pt-3 text-primary font-weight-bold">Date Aller</td>
                                        <td class="pt-3">
                                            {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                                        </td>
                                    </tr>
                                @endif
                                @if($type_voyage != 'aller_simple')
                                    <tr>
                                        <td class="pt-3 text-primary font-weight-bold">Date retour</td>
                                        <td class="pt-3">
                                            @if($type_voyage=='aller_retour_same_vol')
                                                {{\Carbon\Carbon::parse($vol->date_retour)->format("d/m/Y")}}
                                            @elseif($type_voyage=='aller_retour_diffrent_vol')
                                                {{\Carbon\Carbon::parse($vol_retour->date_aller)->format("d/m/Y")}}
                                            @elseif($type_voyage=='retour_simple')
                                                {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="pt-3 text-primary font-weight-bold">Bagages</td>
                                    <td class="pt-3">
                                        {{$vol->soute_num}} x {{$vol->bagage}} kgs + {{$vol->bg_hand}} Kgs
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pt-3 text-primary font-weight-bold">Pssagers</td>
                                    <td class="pt-3">
                                        @foreach($reservation->passagers as $passager)
                                            <p class="mb-0">{{$passager->nom.' '.$passager->prenom}}</p>
                                        @endforeach
                                    </td>
                                </tr>
                                @if(strtolower($flightsGroup->country->nom) == 'one')
                                    <tr>
                                        <td class="pt-3 text-primary font-weight-bold">Assurances</td>
                                        <td class="pt-3 assuences_total"></td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="card-footer pb-0 d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="5" validationNum="2"
                                    id="page5-validation-check1" next="page5-validation-check2">Valider
                            </button>
                        </div>

                    </div>
                    <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef;width: 600px;">
                        <div class="card-header pb-3 pt-0">
                            <div style="height: 30px" class="bg-primary text-center">
                                <h6 class="m-0 font-weight-bold text-light">
                                    Paiement
                                </h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <table style="font-size: 17px">
                                <tr>
                                    <td class="pt-3 text-primary font-weight-bold">Total à payer</td>
                                    <td class="pt-3 total_paye">{{$devisTotalPrice}} Є</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer pb-0 d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="5" validationNum="2"
                                    id="page5-validation-check2">Valider
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-sm-flex align-items-center justify-content-between mt-5">
                <button type="button" class="btn text-light" id="precedant" style="background-color: #fe6219">
                    Precedant
                </button>
                <div class="text-center">
                    <button type="button" class="btn btn-primary " id="next" style="display:none;">
                        Valider
                    </button>
                    <button class="btn btn-primary" id="next2" style="display:none;">
                        Valider
                    </button>
                </div>
            </div>

        </form>
    </div>
    <x-agent.common.image-preview-modal/>

@endsection

@push("script")
    <script>
        $(document).ready(function () {
            $('.submit-form').submit(function (){
                $(".spinner-overlay").css('display','flex');
            })


            $(document).on('change', '.passportfieldfile ', function (event) {
                $(this).parent().children('.custom-file-upload').css({'background': 'green', 'color': 'white'})
                $(this).parent().children('.img_preview').attr('src', URL.createObjectURL(event.target.files[0]))
            })
            $(document).on('keyup', '.passportfield', function () {
            })

            // Check befor move to next page
            $(document).on('click', '.page-validation-check', function () {
                $newClassName = 'page' + $(this).attr('pageNum') + '-validation-checked'
                $(this).addClass('btn-primary ' + $newClassName)
                $(this).removeClass('btn-secondary')
                $('#' + $(this).attr('next')).show()
                if ($(this).attr('validationNum') == $('.' + $newClassName).length) {
                    if ($(this).attr('pageNum') == 5) {
                        $('#next2').show()
                    } else {
                        $('#next').show()
                    }
                }
            })

            $(document).on('click', '#next', function () {
                let num = $('#pagenum').val()
                if (parseInt(num) + 1 != 4) {
                    console.log(parseInt(num) + 1)
                    $(this).hide()
                }
                if (num == 4) {
                    $('#page4').hide()
                    $('#page5').show()
                    $('#pagenum').val(5)

                } else {
                    for (let i = 1; i <= 3; i++) {
                        if (num == i) {
                            $('#page' + i).hide()
                            let newnum = parseInt(i) + 1
                            $('#page' + newnum).show()
                            $('#pagenum').val(newnum)
                            break;
                        }
                    }
                }

            })
            $('#next2').click(function () {
                $(this).hide()
                $('#pagenum').val($('#pagenum').val() - 1)
            })
            $('#precedant').click(function () {
                let num = $('#pagenum').val()
                if (num == 5) {
                    $('#next2').hide()
                    $('#next').show()
                    $("#next").attr('type', 'button')
                }
                for (let i = 1; i <= 5; i++) {
                    if (num == i && num != 1) {
                        $('#page' + i).hide()
                        let newnum = parseInt(i) - 1
                        $('#page' + newnum).show()
                        $('#pagenum').val(newnum)
                        break;
                    }
                }
            })
        })
    </script>
@endpush
