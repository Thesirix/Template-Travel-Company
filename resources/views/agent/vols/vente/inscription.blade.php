@extends('layouts.admin')

@section("styles")
    <style>
        #page1-validation-check1 {
            display: block !important;
        }

        .page-validation-check {
            display: none;
        }

        .error {
            color: red;
            font-size: 15px;
        }

        .plus, .moins, .devis-moins, .devis-plus {
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
            height: 35px;
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

@section('SideBar')
    <x-side-bar />
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection




@section('content')
    <div class="container-fluid mb-5" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                    <li class="breadcrumb-item">{{strtolower($flightsGroup->country->nom)}}</li>
                </ol>
            
        </div>

        <form method="post" action="{{route('agent.vols.vente.store', $flightsGroup->id)}}"
              enctype="multipart/form-data" class="right_col submit-form" role="main">
            @csrf
            <input type="hidden" name="type_reservation" id="type_reservation" value="{{$type_reervation}}">
            <input type="hidden" name="type_voyage" id="type_voyage" value="{{$type_voyage}}">
            <input type="hidden" name="vol_id" value="{{$vol->id}}">
            @if($type_voyage == 'aller_retour_diffrent_vol')
                <input type="hidden" name="vol_retour_id" value="{{$vol_retour->id}}">
            @endif
            <input type="hidden" id="adultcountinput2" class="input" name="adultcountinput2"
                   value="0">
            <input type="hidden" id="enfantcountinput2" class="input" name="enfantcountinput2" value="0">
            <input type="hidden" id="bebecountinput2" class="input" name="bebecountinput2" value="0">

            
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
                                                     :changeDate="false"/>

                <x-agent.vols.inscription.page2.index :$vol/>
                <x-agent.vols.inscription.page3.index :$vol/>
            
            <x-agent.vols.inscription.page4.index :$vol
                                                 :volRetour="$vol_retour"
                                                 :typeVoyage="$type_voyage"
                                                 :destination="$flightsGroup->country->nom"
                                                 :$assurences/>
            <x-agent.vols.inscription.page5.index :$vol
                                                 :volRetour="$vol_retour"
                                                 :typeVoyage="$type_voyage"
                                                 :destination="$flightsGroup->country->nom"
            />

            <div class="d-sm-flex align-items-center justify-content-between mt-5">
                <a href="{{url()->previous()}}" type="button" class="btn text-light" id="precedant"
                   style="background-color: #fe6219">
                    Precedant
                </a>
                <div class="text-center">
                    <button type="button" class="btn btn-primary " id="next" style="display:none;">
                        Valider
                    </button>
                    <button class="btn btn-primary form-submit" id="next2" style="display:none;">
                        Valider
                    </button>
                </div>
            </div>
        </form>

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

        <x-agent.shared.passager-modal/>

    </div>

@endsection

@push("script")
    <script src="{{ asset('assets/js/validation/flightPassengerValidation.js') }}"></script>
    <script>
        // disable button after submit
        $('.form-submit').click(function () {
            $(this).hide(); // disable the button
        });
        $('.submit-form').submit(function () {
            $(".spinner-overlay").css('display', 'flex');
        })

        function validateEmail(email) {
            var re = /\S+@\S+\.\S+/;
            return re.test(email);
        }

        {{--  navigation (next and previous)  --}}
        $(document).on('click', '#next', function () {

            let destination = "{{strtolower($flightsGroup->country->nom)}}";
            let num = $('#pagenum').val();

            if (num == 1) {
                $("#precedant").removeAttr("href");

            }
            $('.error').text('')
            if (num == 4) {
                //other validation
                let error_nb = 0;
                let passager_exist = "yes";
                let data = {
                    nb_adult: $('#adultcountinput').val(), nb_enfnat: $('#enfantcountinput').val(),
                    nb_bebe: $('#bebecountinput').val()
                }
                let allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                for (let i = 1; i <= $('#adultcountinput').val(); i++) {
                    let nom = $('#adult_nom_' + i)
                    let prenom = $('#adult_prenom_' + i)
                    let date_naissance = $('#adult_date_naissance_' + i)

                    let passport = $('#adult_passport_' + i)
                    let passportfile = $('#adult_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();

                    let date_expiration = $('#adult_passport_expiration_' + i)

                    let sejour = $('#adult_sejour_' + i)
                    let sejour_date_expiration = $('#adult_sejour_expiration_' + i)
                    let sejour_en_cour = $('#adult_sejour_en_cour_' + i)
                    let sejourfile = $('#adult_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();

                    let email = $('#adult_email_' + i)
                    let phoneNumber1 = $('#adult_tele1_' + i)

                    data['adult_nom_' + i] = nom.val()
                    data['adult_prenom_' + i] = prenom.val()
                    data['adult_date_' + i] = date_naissance.val()


                    if (nom.val() == '') {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '') {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '') {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }else if (date_naissance.attr('max') !== undefined && (date_naissance.val() > date_naissance.attr('max'))) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal + "<br>")
                        error_nb++
                    }else if (date_naissance.attr('min') !== undefined && (date_naissance.val() < date_naissance.attr('min'))) {
                        let datesplt = date_naissance.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal + "<br>")
                        error_nb++
                    }

                    if (email.val() != '' && !validateEmail(email.val())) {
                        email.parent().children('.error').text('Veuillez fournir une adresse e-mail valide')
                        error_nb++
                    }
                    if (!isValidFlightPassengerPhoneNumber(phoneNumber1.val())) {
                        phoneNumber1.parent().children('.error').text('Téléphone doit commencer par 06 ou 07 et contenir de 8 à 10 chiffres.')
                        error_nb++
                    }

                    if (passport.val() == '') {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_expiration.val() == '') {
                        date_expiration.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + "<br>")
                        error_nb++
                    }

                    if (passportfile.val() == '') {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (passportfile.val() != '' && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }


                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire <br>')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + '<br>')
                        error_nb++
                    }

                    if (sejourfile.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (sejourfile.val() != '' && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                for (let i = 1; i <= $('#enfantcountinput').val(); i++) {

                    let nom = $('#enfant_nom_' + i)
                    let prenom = $('#enfant_prenom_' + i)

                    let date_naissance = $('#enfant_date_naissance_' + i)
                    let passport = $('#enfant_passport_' + i)
                    let passportfile = $('#enfant_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                    let date_expiration = $('#enfant_passport_expiration_' + i)

                    let sejour = $('#enfant_sejour_' + i)
                    let sejour_date_expiration = $('#enfant_sejour_expiration_' + i)
                    let sejour_en_cour = $('#enfant_sejour_en_cour_' + i)
                    let sejourfile = $('#enfant_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();


                    data['enfant_nom_' + i] = nom.val()
                    data['enfant_prenom_' + i] = prenom.val()
                    data['enfant_date_' + i] = date_naissance.val()

                    if (nom.val() == '') {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '') {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '') {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (date_naissance.attr('max') !== undefined && (date_naissance.val() > date_naissance.attr('max'))) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal + "<br>")
                        error_nb++
                    }else if (date_naissance.attr('min') !== undefined && (date_naissance.val() < date_naissance.attr('min'))) {
                        let datesplt = date_naissance.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal + "<br>")
                        error_nb++
                    }

                    if (passport.val() == '') {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_expiration.val() == '') {
                        date_expiration.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + "<br>")
                        error_nb++
                    }
                    if (passportfile.val() == '') {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (passportfile.val() != '' && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }

                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }

                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire <br>')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + '<br>')
                        error_nb++
                    }

                    if (sejourfile.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (sejourfile.val() != '' && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                for (let i = 1; i <= $('#bebecountinput').val(); i++) {
                    let nom = $('#bebe_nom_' + i)
                    let prenom = $('#bebe_prenom_' + i)
                    let date_naissance = $('#bebe_date_naissance_' + i)

                    let passport = $('#bebe_passport_' + i)
                    let passportfile = $('#bebe_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                    let date_expiration = $('#bebe_passport_expiration_' + i)

                    let sejour = $('#bebe_sejour_' + i)
                    let sejour_date_expiration = $('#bebe_sejour_expiration_' + i)
                    let sejour_en_cour = $('#bebe_sejour_en_cour_' + i)
                    let sejourfile = $('#bebe_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();


                    data['bebe_nom_' + i] = nom.val()
                    data['bebe_prenom_' + i] = prenom.val()
                    data['bebe_date_' + i] = date_naissance.val()

                    if (nom.val() == '') {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '') {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '') {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }else if (date_naissance.attr('max') !== undefined && (date_naissance.val() > date_naissance.attr('max'))) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal + "<br>")
                        error_nb++
                    }else if (date_naissance.attr('min') !== undefined && (date_naissance.val() < date_naissance.attr('min'))) {
                        let datesplt = date_naissance.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal + "<br>")
                        error_nb++
                    }

                    if (passport.val() == '') {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_expiration.val() == '') {
                        date_expiration.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + "<br>")
                        error_nb++
                    }

                    if (passportfile.val() == '') {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (passportfile.val() != '' && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }

                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }

                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire .<br>')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal + '<br>')
                        error_nb++
                    }

                    if (sejourfile.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (sejourfile.val() != '' && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                $.ajax({
                    type: 'get',
                    async: false,
                    url: '{{ route('agent.vols.vente.check-passenger', $flightsGroup->id) }}',
                    data,
                    success: function (data) {
                        console.log(data)
                        if (data == 'exist') {
                            $('.exist_message').text('Les passagers est deja enregistré')
                            $('#error_modal').modal('show');
                            passager_exist = "yes"
                        } else {
                            passager_exist = "no"
                        }
                    },

                });
                console.log('heere' + error_nb)
                if ((error_nb == 0 && passager_exist == "no" && ($('#enfantcountinput').val() > 0 || $('#adultcountinput').val() > 0))) {

                    //show submit button and hide next button
                    $(this).hide()

                    let montnt_acompte;
                    @if($type_reervation=='Reservation')
                        montnt_acompte = (parseInt($('#adultcountinput').val()) + parseInt($('#enfantcountinput').val())) * parseInt({{$vol->total_a_payer}})
                    @else
                        montnt_acompte = (parseInt($('#adultcountinput').val()) + parseInt($('#enfantcountinput').val())) * 100
                    @endif
                    $('#montant_acompte').attr('min', montnt_acompte)
                    $('#montant_acompte').val(montnt_acompte)
                    $('.paiement_inscription').text(montnt_acompte + " €")

                    $('.error').text('')
                    $('#page4').hide()
                    $('#page5').show()
                    $('#pagenum').val(5)

                    $('.assuences_total').html('')
                    $('.Recapitulatif_passagers').html('')
                    let prixtotal = 0
                    @foreach($assurences as $assurence)
                    let assurance_{{$assurence->id}}= 0
                    @endforeach

                    

                    for (let i = 1; i <= $('#adultcountinput').val(); i++) {
                        let text = '<p class="mb-0">' + $('#adult_nom_' + i).val() + ' ' + $('#adult_prenom_' + i).val() + "(ADT)</p>"
                        $('.Recapitulatif_passagers').append(text)
                        prixtotal += {{$prix_adult}}

                        @foreach($assurences as $assurence)
                        if($("input[name2='adult_assurance_" + i + "']:checked").val() == '{{$assurence->id}}')
                        {
                            assurance_{{$assurence->id}}++
                            prixtotal += {{$assurence->prix}}
                        }
                        @endforeach
                    }
                    for (let i = 1; i <= $('#enfantcountinput').val(); i++) {
                        let text = '<p class="mb-0">' + $('#enfant_nom_' + i).val() + ' ' + $('#enfant_prenom_' + i).val() + "(CHD)</p>"
                        $('.Recapitulatif_passagers').append(text)
                        prixtotal += {{$prix_enfant}}
                        @foreach($assurences as $assurence)
                        if($("input[name2='enfant_assurance_" + i + "']:checked").val() == '{{$assurence->id}}')
                        {
                            assurance_{{$assurence->id}}++
                            prixtotal += {{$assurence->prix}}
                        }
                        @endforeach
                    }
                    for (let i = 1; i <= $('#bebecountinput').val(); i++) {
                        let text = '<p class="mb-0">' + $('#bebe_nom_' + i).val() + ' ' + $('#bebe_prenom_' + i).val() + "(INF)</p>"
                        $('.Recapitulatif_passagers').append(text)
                        prixtotal += {{$prix_bebe}}
                        @foreach($assurences as $assurence)
                        if($("input[name2='bebe_assurance_" + i + "']:checked").val() == '{{$assurence->id}}')
                        {
                            assurance_{{$assurence->id}} = assurance_{{$assurence->id}} + 1
                            prixtotal += {{$assurence->prix}}
                        }
                        @endforeach
                    }
                    let textasurance
                    @foreach($assurences as $assurence)
                    if (assurance_{{$assurence->id}} > 0) {
                        textasurance = '{{$assurence->name}}' + ' x ' + assurance_{{$assurence->id}}
                        $('.assuences_total').append('<p class="mb-0">' + textasurance + '</p>')
                    }
                    @endforeach
                }
                // alert()
            } else {
                for (let i = 1; i <= 3; i++) {
                    if (num == i) {
                        $('#page' + i).hide()
                        let newnum = parseInt(i) + 1
                        $('#page' + newnum).show()
                        $('#pagenum').val(newnum)
                        // }
                        break;
                    }
                }
                if (num != 3) {
                    $(this).hide()
                }
            }
        })
        $('#precedant').click(function () {
            let num = $('#pagenum').val()
            if (num == 1) {
                $(this).attr("href", "{{url()->previous()}}")
            }
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

        // Check if all condition accepted befor move to next page
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

        {{--    passager & chambres managment (add and remove)--}}
        $(document).ready(function () {
            // chamres managment
            

            // calcul devis
            function calculDevisPrixTotal() {
                let nbAdults = parseInt($('#adultcountinput2').val())
                let nbEnfants = parseInt($('#enfantcountinput2').val())
                let nbBebes = parseInt($('#bebecountinput2').val())
                let destination = "{{strtolower($flightsGroup->country->nom)}}"

                let priceTotalAdult = 0
                let priceTotalEnfant = 0
                let priceTotalBebe = 0

                let prix_total = 0

                
                    priceTotalAdult = (nbAdults * {{$vol->prix_vente_adult}})
                priceTotalEnfant = (nbEnfants * {{$vol->prix_vente_enfant}})
                priceTotalBebe = (nbBebes * {{$vol->prix_vente_bebe != null ? $vol->prix_vente_bebe : 0}})

                prix_total = priceTotalAdult + priceTotalEnfant + priceTotalBebe;

                @if($vol_retour)
                    priceTotalAdult = (nbAdults * {{$vol_retour->prix_vente_adult}})
                priceTotalEnfant = (nbEnfants * {{$vol_retour->prix_vente_enfant}})
                priceTotalBebe = (nbBebes * {{$vol_retour->prix_vente_bebe}})
                prix_total += priceTotalAdult + priceTotalEnfant + priceTotalBebe;
                @endif

                

                $('.devis-prix-total-adult').text(priceTotalAdult + ' €')
                $('.devis-prix-total-enfant').text(priceTotalEnfant + ' €')
                $('.devis-prix-total-bebe').text(priceTotalBebe + ' €')

                $('.devis-prix-total').text(prix_total + ' €');

            }

            // add passager
            $('.plus').click(function () {
                console.log('clicked')
                let nbtotal = parseInt($('#adultcountinput2').val()) + parseInt($('#enfantcountinput2').val())
                let nbPassagers = $('#nbPassagers').val()
                let type = $(this).parent().attr('type')
                let oldnum = $(this).parent().children('.input').val()

                let destination = "{{strtolower($flightsGroup->country->nom)}}";
                if (true) {

                    let newnum = parseInt(oldnum) + 1
                    let i = parseInt(nbPassagers) + 1
                    let typeReservation = $('#type_reservation').val()

                    let bgColor, dateNaissanceMinDate, dateNaissanceMaxDate;
                    let passportMinDate, sejourMinDate;

                    @if($type_voyage=='aller_retour_same_vol')
                        passportMinDate = '{{\Carbon\Carbon::parse($vol->date_retour)->addMonths(6)->format('Y-m-d')}}'
                    sejourMinDate = '{{\Carbon\Carbon::parse($vol->date_retour)->format('Y-m-d')}}'
                    @elseif($type_voyage=='aller_retour_diffrent_vol')
                        passportMinDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->addMonths(6)->format('Y-m-d')}}';
                    sejourMinDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->format('Y-m-d')}}';
                    @elseif(in_array($type_voyage,['retour_simple','aller_simple']))
                        passportMinDate = '{{\Carbon\Carbon::parse($vol->date_aller)->addMonths(6)->format('Y-m-d')}}';
                    sejourMinDate = '{{\Carbon\Carbon::parse($vol->date_aller)->format('Y-m-d')}}';
                    @endif

                    if (type == 'adult') {
                        bgColor = '#5656e7'
                        @if($type_voyage=='aller_retour_same_vol')
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol->date_retour)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @elseif($type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @else
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @endif

                    } else if (type == 'enfant') {
                        bgColor = '#f8953c'
                        @if($type_voyage=='aller_retour_same_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol->date_retour)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol->date_retour)->subYears(2)->format('Y-m-d')}}';
                        @elseif($type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @elseif(in_array($type_voyage,['retour_simple','aller_simple']))
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol->date_aller)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($vol->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @endif
                    } else {
                        bgColor = '#e74ac6'

                        @if($type_voyage=='aller_retour_same_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol->date_retour)->subYears(2)->format('Y-m-d')}}';
                        @elseif($type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol_retour->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @elseif(in_array($type_voyage,['retour_simple','aller_simple']))
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($vol->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @endif

                    }
                    $html = '<div class="my-3 mx-3 ' + type + '_div_' + newnum + '" style="border:solid 2px ' + bgColor + ';width: 400px">' +
                        '<p class="text-light text-center py-3" style="font-size:20px;background-color: ' + bgColor + '">' +
                        type.toUpperCase() + ' ' + i + '</p>' +
                        '<div class="py-2 px-1">' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pt-3 pl-3">Genre</td>' +
                        '<td class="pt-3">' +
                        '<input type="hidden" name="passagers[' + i + '][type]" value="' + type + '" />' +
                        '<select required="required" class="form-control" name="passagers[' + i + '][genre]" style="height:35px!important">' +
                        '<option value="male">Homme</option>' +
                        '<option value="female">Femme</option>' +
                        '</select>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Nom</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" type="text" id="' + type + '_nom_' + newnum + '" name="passagers[' + i + '][nom]" class="form-control nom" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Prenom</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" type="text" id="' + type + '_prenom_' + newnum + '" name="passagers[' + i + '][prenom]" class="form-control prenom" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Date naissance</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" ' + (dateNaissanceMinDate && 'min="' + dateNaissanceMinDate + '"') + ' ' + (dateNaissanceMaxDate && 'max="' + dateNaissanceMaxDate + '"') + ' type="date"  name="passagers[' + i + '][date_naissance]"  id="' + type + '_date_naissance_' + newnum + '" class="form-control date_naissance" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        
                            '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">N Passport</td>' +
                        '<td class="pt-3">' +
                        '   <div class="iti iti--allow-dropdown">' +
                        '<input  type="text" name="passagers[' + i + '][passport]"  id="' + type + '_passport_' + newnum + '" class="form-control passportfield" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Download passport</td>' +
                        ' <td class="pt-3"> ' +
                        '<input  type="file" name="passagers[' + i + '][passport_file]" id="' + type + '_passport_file_' + newnum + '" class=" passportfieldfile " style="height:35px!important; ">' +
                        '<label for="' + type + '_passport_file_' + newnum + '" class="custom-file-upload">' +
                        '<i class="fa fa-cloud-upload"></i> Custom Upload' +
                        '</label>' +
                        '  <div class="img_preview" ></div>' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Date expiration</td>' +
                        ' <td class="pt-3"> ' +
                        ' <input type="date"  min="' + passportMinDate + '"  name="passagers[' + i + '][expiration]" id="' + type + '_passport_expiration_' + newnum + '" class="form-control date_expiration" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">N Titre Séjour</td>' +
                        '<td class="pt-3">' +
                        '<input  type="text" name="passagers[' + i + '][sejour]" id="' + type + '_sejour_' + newnum + '" class="form-control sejourfield" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Download Titre Séjour</td>' +
                        ' <td class="pt-3"> ' +
                        '<input  type="file" name="passagers[' + i + '][sejour_file]" id="' + type + '_sejour_file_' + newnum + '" class=" passportfieldfile " style="height:35px!important;visibility:hidden ">' +
                        '<label for="' + type + '_sejour_file_' + newnum + '" class="custom-file-upload">' +
                        '<i class="fa fa-cloud-upload"></i> Custom Upload' +
                        '</label>' +
                        '  <div class="img_preview" ></div>' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Date expiration</td>' +
                        ' <td class="pt-3"> ' +
                        '   <input  min="' + sejourMinDate + '" type="date" id="' + type + '_sejour_expiration_' + newnum + '" name="passagers[' + i + '][sejour_expiration]"  class="form-control date_expiration" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Titre Séjour en cours</td>' +
                        ' <td class="pt-3">' +
                        '   <input type="checkbox" name="passagers[' + i + '][sejour_en_cour]" id="' + type + '_sejour_en_cour_' + newnum + '" class="pl-3 ' + type + '_sejour_en_cour_" >' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '</table>';
                    if (type == 'adult') {
                        $html += '<table class="px-3 py-2 mb-2 " style="border:solid 1px orange;width:100%">' +
                            '<tr>' +
                            '<td class="pl-3"> Telephone 1 </td>' +
                            '<td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele1]" id="' + type + '_tele1_' + newnum + '" class="form-control tele tele_1" style="height:35px!important; " >' +
                            ' <p class="error"></p>' +
                            '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td class="pl-3"> Telephone 2 </td>' +
                            '<td class="pt-3">' +
                            ' <input  type="tel" name="passagers[' + i + '][tele2]" id="' + type + '_tele2_' + newnum + '" class="form-control tele" style="height:35px!important; " >' +
                            ' <p class="error"></p>' +
                            ' </td>' +
                            ' </tr>' +
                            '<tr>' +
                            ' <td class="pl-3">Téléphone sur place</td>' +
                            ' <td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele_destination]"   class="form-control tele" style="height:35px!important; ">' +
                            '</td>' +
                            '</tr>' +
                            '<tr>' +
                            ' <td class="pl-3">Email</td>' +
                            ' <td class="pt-3 pb-3">' +
                            '<input type="email" id="' + type + '_email_' + newnum + '" name="passagers[' + i + '][email]" class="form-control " style="height:35px!important; " >' +
                            ' <p class="error"></p>' +
                            '</td>' +
                            '</tr>' +
                            '</table>';
                    } else {
                        $html += '<table class="px-3 py-2 mb-2 d-none" style="border:solid 1px orange;width:100%">' +
                            '<tr>' +
                            '<td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele1]" id="passagers[' + i + '][tele1]" class="form-control tele tele_1" style="height:35px!important; ">' +
                            ' <input  type="tel" name="passagers[' + i + '][tele2]" id="passagers[' + i + '][tele2]" class="form-control tele" style="height:35px!important; ">' +
                            ' <input  type="tel" name="passagers[' + i + '][tele2]" id="passagers[' + i + '][tele2]" class="form-control tele" style="height:35px!important; ">' +
                            '<input  type="tel" name="passagers[' + i + '][tele_destination]" id="passagers[' + i + '][tele_destination]"  class="form-control tele" style="height:35px!important; ">' +
                            '<input  type="email" name="passagers[' + i + '][email]" class="form-control " style="height:35px!important; ">' +
                            '</td>' +
                            '</tr>' +
                            '</table>';
                    }
                    $html += '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">Assistance specifique</td>' +
                        '<td class="pt-3">' +
                        '<input  type="text" name="passagers[' + i + '][assistance]" class="form-control" style="height:35px!important; ">' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td  class="pl-3">Observation</td>' +
                        '<td class="pt-3">' +
                        '<textarea  style="height:38px;" name="passagers[' + i + '][observation]" class="form-control" ></textarea>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Dossier</td>' +
                        '<td class="pt-3">' +
                        '<label for="' + type + '_dossier_etat_' + i + '1">Complet</label><input type="radio" name="passagers[' + i + '][dossier_etat]"  id="' + type + '_dossier_etat_' + i + '1" value="Complet" class="mr-3 ml-2"/>' +
                        '<label for="' + type + '_dossier_etat_' + i + '2">Incomplet</label><input checked type="radio" name="passagers[' + i + '][dossier_etat]" id="' + type + '_dossier_etat_' + i + '2" value="Incomplet" class="ml-2"/>' +
                        '</td>' +
                        '</tr>' +
                        '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%;display:none">' +
                        @foreach($assurences as $assurence)
                            '<tr class="pr-5">' +
                        '<td class="pt-3 pl-3">{{$assurence->name}}</td>' +
                        '<td class="pt-3">{{$assurence->prix}} € </td>' +
                        '<td class="pt-3">' +
                        '<input type="radio" @if($assurence->prix==0) checked @endif name="passagers[' + i + '][assurance]" id="assurance" name2="' + type + '_assurance_' + newnum + '" value="{{$assurence->id}}" >' +
                        '</td>' +
                        '</tr>' +
                        '<tr><td class="pb-3"></td></tr>' +
                        @endforeach
                            '</table>' +
                        ' </div>' +
                        '</div>';

                    if (type == 'bebe' && oldnum < $('#adultcountinput').val()) {
                        $('#nbPassagers').val(parseInt(nbPassagers) + 1)

                        $(this).parent().children('.input').val(newnum)
                        $(this).parent().children('.nbtext').text(newnum)
                        $('#' + $(this).attr('target')).val(newnum)
                        $('.devis-' + type + 'count').text(newnum)
                        calculDevisPrixTotal()
                        $('#passagersinputs').append($html)
                    } else if (type != 'bebe' && ((nbtotal < {{$vol->place_disponible}} && (typeReservation == 'Reservation'))
                        || $('#type_reservation').val() == 'liste_attente')) {
                        $('#nbPassagers').val(parseInt(nbPassagers) + 1)

                        $(this).parent().children('.input').val(newnum)
                        $(this).parent().children('.nbtext').text(newnum)
                        $('#' + $(this).attr('target')).val(newnum)
                        $('.devis-' + type + 'count').text(newnum)
                        calculDevisPrixTotal()

                        $('#passagersinputs').append($html)
                    }
                }
            })

            // remove passager
            $('.moins').click(function () {
                let type = $(this).parent().attr('type')
                if (type == 'bebe') {
                    let nbebeval = $('#bebecountinput').val()
                    if (nbebeval > 0) {
                        $('.bebe_div_' + nbebeval).remove()
                    }
                } else if (type == 'enfant') {
                    let nbenfantval = $('#enfantcountinput').val()
                    if (nbenfantval > 0) {
                        $('.enfant_div_' + nbenfantval).remove()
                    }
                } else if (type == 'adult') {
                    let nbadultval = $('#adultcountinput').val()
                    if (nbadultval > 0) {
                        $('.adult_div_' + nbadultval).remove()
                        if ($('#adultcountinput').val() == $('#bebecountinput').val()) {
                            $('.bebe_div_' + nbadultval).remove()
                            $('#bebecount').text(nbadultval - 1)
                            $('#bebecountinput').val(nbadultval - 1)
                        }
                    }
                }


                let oldnum = $(this).parent().children('.input').val()
                if (oldnum >= 1) {
                    let newnum = parseInt(oldnum) - 1
                    $(this).parent().children('.input').val(newnum)
                    $(this).parent().children('.nbtext').text(newnum)
                    $('#' + $(this).attr('target')).val(newnum)
                    $('.devis-' + type + 'count').text(newnum)
                    calculDevisPrixTotal()

                }
            })
        })

    </script>


    <script>


        $(document).ready(function () {

            @if(session()->get('already_enregistred')=='already_enregistred')
            $('.exist_message').text("le nombre disponible de vol < nombre de passagers")
            $('#error_modal').modal('show')
            @endif
            ////Passpoert Events////
            $(document).on('click', '.pasport_file', function () {
                $('#image_preview_mdal_img').attr('src', $(this).attr('src'))
                $('#image_preview_mdal').modal('show')
            })
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
            $(document).on('keyup', '.passportfield', function () {
            })
            ////End Passpoert Events////


        })
    </script>

@endpush
