@extends('layouts.admin')

@section('SideBar')
    <x-side-bar />
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('styles')
    <style>
        .clearfix:after, form:after {
            display: none !important;
        }

        .custum_table td, .custum_table th {
            text-align: start;
            width: 200px;
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .table td, .table th {
            padding-bottom: 7px;
            padding-top: 7px;
            padding-left: 10px;
            text-align: start;
        }

        td, th {
            text-align: start;
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

        .error {
            color: red;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid mb-5" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Consultation </h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Emission</li>
                <li class="breadcrumb-item">Consultation</li>
                <li class="breadcrumb-item">Consulte</li>
            </ol>
        </div>

        <a href="{{route('agent.vols.emission.flight', [$flightsGroup->id, $reservation->vol_id])}}"
           class="btn btn-sm mb-3 btn-primary">Précédant</a>

        <div class="d-flex flex-wrap justify-content-between flex-fill">
            <form method="post"
                action="{{route('agent.vols.emission.reservation.update', $flightsGroup->id)}}"
                class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
                @csrf
                <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
                <div class="card-header pb-3 pt-0">
                    <div style="height: 30px" class="bg-primary text-center">
                        <h6 class="m-0 font-weight-bold text-light">
                            Reservation
                        </h6>
                    </div>
                </div>
                <div class="card-body " style="">
                    <table class="custum_table" style="width: 450px">
                        <tr>
                            <td style="width: 40%">N° Réservation</td>
                            <td class="d-flex justify-content-between align-items-center">
                                {{substr($reservation->passagers[0]['nom'],0,3).''.$reservation->id}}
                            </td>
                        </tr>
                        <tr>
                            <td>Date Réservation</td>
                            <td>{{ \App\Support\Dates::format($reservation->created_at) }}</td>
                        </tr>
                        <tr>
                            <td>Agent</td>
                            <td class="d-flex align-items-center">
                                <div id="agent-section">
                                    {{$reservation->gent->name}} {{$reservation->gent->prenom}}
                                    <button type="button" class="btn btn-primary" id="display-agent-form">
                                        Modifier
                                    </button>
                                </div>
                                <div id="agent-form" style="display: none">
                                    <select class="form-control" name="reservationAgent">
                                        @foreach($agents as $agent)
                                            <option value="{{$agent->id}}"
                                                    @if($reservation->gent->id== $agent->id) selected @endif
                                            >
                                                {{$agent->name}} {{$agent->prenom}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Itineraire</td>
                            <td>{{$reservation->vol->pnr}}</td>
                        </tr>
                        @if($reservation->type_voyage != 'retour_simple')
                            <tr>
                                <td>Date Aller</td>
                                <td class="d-flex justify-content-between align-items-center">
                                    {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                    <a href="{{ route('agent.vols.reservations.change-flight', [$flightsGroup->id, $reservation->id]) }}"
                                       class="btn btn-primary">Modifier</a>
                                </td>
                            </tr>
                        @endif
                        @if($reservation->type_voyage != 'aller_simple')
                            <tr>
                                <td>Date Retour</td>
                                <td>
                                    @if($reservation->type_voyage=='aller_retour_same_vol')
                                        {{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}
                                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                                        {{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('d/m/Y')}}
                                    @elseif($reservation->type_voyage=='retour_simple')
                                        {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Numero Facture</td>
                            <td>
                                <div id="bill-section">
                                    {{$reservation->bill?->n_acompte}}
                                    <button type="button" class="btn btn-primary" id="display-bill-form">
                                        Modifier
                                    </button>
                                </div>
                                <div id="bill-form" style="display: none">
                                    <input type="number" name="billNumber" id="billNumber" class="form-control"
                                           value="{{$reservation->bill?->n_acompte}}" min="400000" max="600000">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    <button type="button" class="btn btn-danger" id="hide-bill-form"
                                            n_acompte="{{$reservation->bill?->n_acompte}}">
                                        Annuler
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            @foreach($reservation->acomptes as $acompte)
                <x-agent.vols.common.edit-reservation-payment-card :$flightsGroup
                                                                  :$acompte
                                                                  :$loop
                                                                  :reservationId="$reservation->id"
                                                                  :reservationTotalPrice="$reservation->prix_total"
                                                                  :reservationPayedAmount="$reservation->prix_payed"

                />
            @endforeach
            <x-agent.vols.common.add-reservation-payment-card :$flightsGroup
                                                             :reservationId="$reservation->id"
                                                             :reservationTotalPrice="$reservation->prix_total"
                                                             :reservationPayedAmount="$reservation->prix_payed"
            />
        </div>
        <form action="{{ route('agent.vols.emission.passengers.update', $flightsGroup->id) }}"
              id="passagers_form" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
            <div class="d-flex flex-wrap flex-fill " id="passagersinputs">
                @foreach($reservation->passagers as $person)
                    <div class="my-3 mx-3"
                         style="border:solid 2px @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif;width: 400px"
                         id="passager_{{$person->id}}">
                        <p class="text-light d-flex align-items-center justify-content-between px-4 py-3"
                           style="font-size:20px;background-color: @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif ">
                            <button class="btn btn-primary modify_passager" type="button"
                                    passager_id="{{$person->id}}"
                                    passager_type="{{$person->type}}">
                                Modifier
                            </button>
                            <button class="btn btn-primary enregister_passager"
                                    style="display:none;">Enregistrer
                            </button>
                            {{$person->type}} @if($person->type=='bebe')
                                < 2
                                ans
                            @elseif($person->type=='enfant')
                                <
                                12 ans
                            @endif
                            @if($person->verifier=='NON')
                                <button class="btn btn-primary veriferutton" type="button"
                                        passager_id="{{$person->id}}">
                                    Verifer
                                </button>
                            @else
                                <button class="btn btn-success">Ok</button>
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
        </form>
    </div>
    <div class="modal fade" id="error_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center" style="font-size: 20px">
                    <img src="{{asset('sys/warning.svg')}}" style="height: 40px">
                    <p class="exist_message ml-3"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="success_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center" style="font-size: 20px">
                    <img src="{{asset('sys/check.png')}}" style="height: 40px">
                    <p class="success_message ml-3"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="verifer_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post"
                      action="{{route('agent.vols.emission.verify', $flightsGroup->id)}}"
                      class="modal-body submit-form"
                      style="font-size: 20px">
                    @csrf
                    <div class="d-flex align-items-center">
                        <img src="{{asset('sys/warning.svg')}}" style="height: 40px">
                        <input type="hidden" name="passager_verifer_id" id="passager_verifer_id">
                        <p class="mb-0 ml-3">Verifer ce passager ?</p>
                    </div>
                    <div class="text-right mt-4">
                        <button class="btn btn-primary text-right">Verifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-agent.common.image-preview-modal/>

    <x-agent.vols.common.delete-passenger-passport-modal :groupId="$flightsGroup->id"/>

@endsection

@push("script")
    <script src="{{ asset('assets/js/validation/flightPassengerValidation.js') }}"></script>
    <script src="{{asset('assets/js/agent/vols/common/reservation-infos.js')}}"></script>
    <script src="{{ asset('assets/js/agent/vols/common/payment-management.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.submit-form').submit(function () {
                $(".spinner-overlay").css('display', 'flex');
            })

            $('.veriferutton').click(function () {
                $('#passager_verifer_id').val($(this).attr('passager_id'))
                $('#verifer_modal').modal('show')
            })

            $('#montant').on("change keyup", function () {
                $('.reste_paye').text({{$reservation->prix_total-$reservation->prix_payed}} - $(this).val() + " €")
            })
            $('.add_paye').click(function () {
                $('#payement2_section').show()
            })


            $('.modify_passager').click(function () {
                $(this).hide();
                $(this).parent().children('.enregister_passager').show()
            })

            @if(session()->get('already_enregistred')=='already_enregistred')
            $('.exist_message').text("le nombre disponible de vol < nombre de passagers")
            $('#error_modal').modal('show')
            @endif

            @if(session()->get('etat')=='success')
            $('.success_message').text("Success")
            $('#success_modal').modal('show')
            @endif

            $(document).on('click', '.modify_passager', function () {
                let id = $(this).attr('passager_id')
                $('.input_' + id).removeAttr('readonly')
                $('.input_' + id).removeAttr('disabled')
            })

            $('#passagers_form').submit(function (e) {
                let error_nb = 0;
                $('.error').text('')
                let passager_exist = "yes";
                let data = {
                    nb_adult: $('#adultcountinput').val(), nb_enfnat: $('#enfantcountinput').val(),
                    nb_bebe: $('#bebecountinput').val()
                }
                let nom;
                let prenom
                let date_naissance
                let passport
                let passportfile
                let date_expiration
                let sejour
                let sejour_date_expiration
                let sejour_en_cour
                let sejourfile
                let phoneNumber1;
                @foreach($reservation->passagers as $passager)
                    nom = $('#old_{{$passager->type}}_nom_{{$passager->id}}')
                prenom = $('#old_{{$passager->type}}_prenom_{{$passager->id}}')
                date_naissance = $('#old_{{$passager->type}}_date_naissance_{{$passager->id}}')
                passport = $('#old_{{$passager->type}}_passport_{{$passager->id}}')
                passportfile = $('#old_{{$passager->type}}_passport_file_{{$passager->id}}')
                date_expiration = $('#old_{{$passager->type}}_expiration_{{$passager->id}}')
                sejour = $('#old_{{$passager->type}}_sejour_{{$passager->id}}')
                sejour_date_expiration = $('#old_{{$passager->type}}_sejour_expiration_{{$passager->id}}')
                sejour_en_cour = $('#old_{{$passager->type}}_sejour_en_cour_{{$passager->id}}')
                sejourfile = $('#old_{{$passager->type}}_sejour_file_{{$passager->id}}')

                data['old_{{$passager->type}}_nom_{{$passager->id}}'] = nom.val()
                data['old_{{$passager->type}}_prenom_{{$passager->id}}'] = prenom.val()
                data['old_{{$passager->type}}_date_{{$passager->id}}'] = date_naissance.val()
                if (nom.val() == '' || nom.val() == null) {
                    nom.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                if (prenom.val() == '' || prenom.val() == null) {
                    prenom.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_naissance.val() == '' || date_naissance.val() == null) {
                    date_naissance.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_naissance.attr('max')) {
                    if (date_naissance.val() > date_naissance.attr('max')) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        console.log(datefinal)
                        date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal)
                        error_nb++
                    }
                }
                if (date_naissance.attr('min')) {
                    if (date_naissance.val() < date_naissance.attr('min')) {
                        let datesplt = date_naissance.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal)
                        error_nb++
                    }
                }

                @if($passager->type== 'adulte')
                    phoneNumber1 = $('#old_{{$passager->type}}_tele1_{{$passager->id}}');
                if (!isValidFlightPassengerPhoneNumber(phoneNumber1.val())) {
                    phoneNumber1.parent().children('.error').text('Téléphone doit commencer par 06 ou 07 et contenir de 8 à 10 chiffres.');
                    error_nb++;
                    console.log(phoneNumber1);
                }
                @endif

                if (passport.val() == '' || passport.val() == null) {
                    passport.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_expiration.val() < date_expiration.attr('min')) {
                    let datesplt = date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                    date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++

                }
                if ((passportfile.val() == '' || passportfile.val() == null) && passportfile.parent().children('.img_preview').html() == '') {
                    passportfile.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_expiration.val() == '' || date_expiration.val() == null) {
                    date_expiration.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }

                if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                    sejour.parent().children('.error').append('ce champ est obligatoire')
                    error_nb++
                }
                if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                    let datesplt = sejour_date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                    sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++
                } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                    let datesplt = sejour_date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                    sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++
                }

                if ((sejourfile.val() == '' || sejourfile.val() == null) && sejourfile.parent().children('.img_preview').html() == '' && !sejour_en_cour.is(':checked')) {
                    sejourfile.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                @endforeach


                if (error_nb != 0 || ($('#enfantcountinput').val() <= 0 && $('#adultcountinput').val() <= 0)) {
                    e.preventDefault();
                } else {
                    $(".spinner-overlay").css('display', 'flex');
                    $('.input').removeAttr('disabled')
                    $('.input').removeAttr('readonly')
                }


            })


            // })
            // })

            $(document).on('change', '.passportfieldfile ', function (event) {
                $(this).parent().children('.custom-file-upload').css({'background': 'green', 'color': 'white'})
                let forma = event.target.files[0]['name'].toLowerCase().split(".")
                forma = forma[forma.length - 1]
                if (forma == "pdf") {
                    $(this).parent().children('.img_preview').html('<embed  src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px;"/>')
                } else if (["jpg", "png", "jpeg"].includes(forma)) {
                    $(this).parent().children('.img_preview').html('<img src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px"/>')
                }
            })


            $('#edit_passager').click(function () {
                $('#passagers_form').show()
            })


            //
            //         }
            //     }
            // })
        })

    </script>
@endpush
