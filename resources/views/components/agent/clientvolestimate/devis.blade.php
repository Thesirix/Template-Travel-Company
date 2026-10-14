@props(['favorites'])
<div class="card my-3">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between accordion-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <img src="{{ asset('sys/devis.png') }}" alt="Icone Devis"
                style="width: 60px; height: 60px; margin-right: 10px;">

            Devis
        </h6>
    </div>
    <div class="accordion-content">
        <div class="card-body">
            <form id="devisForm" action="{{ route('agent.demandes.devis.store') }}" method="POST">
                @csrf
                <input type="hidden" name="destination" id="hidden_destination">
                <input type="hidden" name="formule" id="hidden_formule">
                <input type="hidden" name="client_id" id="client_id" value="{{ $clientId ?? '' }}">

                <div class="row align-items-center mt-3">
                    <label for="Compagnie" class="mb-0 col-2 "><strong>Compagnie</strong></label>
                    <select class="form-control col-5 " name="Compagnie" id="Compagnie" required>

                    </select>
                </div>


                <div class="row align-items-center mt-3">
                    <label for="Installation" class="mb-0 col-2 "><strong>Installation</strong></label>
                    <select class="form-control col-5" name="Installation" id="Installation" required>

                        <option value="Fauteuil">Fauteuil</option>
                        <option value="Cabine">Cabine</option>
                        <option value="Couchette">Couchette</option>

                    </select>
                </div>
                <div class="row align-items-center mt-3">
                    <label for="Véhicule" class="mb-0 col-2 "><strong>Véhicule</strong></label>
                    <select class="form-control col-5" type="text" name="Véhicule" id="Véhicule" required>

                        <option value="Sans">Sans</option>
                        <option value="Voiture">Voiture</option>
                        <option value="Fourgon">Fourgon</option>
                        <option value="Moto">Moto</option>

                    </select>
                </div>

                <div class="row align-items-center mt-3" id="bagage-row-devis">
                    <label for="bagage" class="mb-0 col-2 "><strong>Bagage</strong></label>
                    <select class="form-control col-5" type="text" name="bagage" id="bagage-devis" required>

                        <option value="Sans">Sans</option>
                        <option value="Avec">Avec</option>


                    </select>
                </div>


                <div class="row align-items-center mt-3">
                    <label for="departure_city" class="mb-0 col-2 "><strong>Ville départ</strong></label>
                    <select class="form-control col-5" name="depart" id="city_depart_devis" required>
                        <option disabled>─── Favoris ───</option>
                        @foreach ($favorites as [$city, $code])
                        <option value="{{ $city }}">{{ $city }} ({{ $code }})</option>
                    @endforeach

                    </select>
                </div>

                <div class="row align-items-center mt-3">
                    <label for="arrived_city" class="mb-0 col-2 "><strong>Ville arrivée</strong></label>
                    <select class="form-control col-5 arrival_city" name="arrived_city" id="arrival_city_devis" required
                        style="visibility: hidden;">

                    </select>
                </div>

                <div class="row align-items-center mt-3">
                    <label for="departure_date" class="mb-0 col-2"><strong>Date d'aller</strong></label>
                    <div class="col-5" style="position: relative;padding: 0;">
                        <input class="form-control date-input" type="text" name="devis_departure_date"
                            id="devis_departure_date" placeholder="JJ/MM/AAAA" required style="padding-right: 40px;">
                        <i class="fa fa-calendar input-date-icon" target="#devis_departure_date"
                            style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="row align-items-center mt-3">
                    <label for="return_date" class="mb-0 col-2"><strong>Date Retour</strong></label>
                    <div class="col-5" style="position: relative;padding: 0;">
                        <input class="form-control date-input" type="text" name="devis_return_date"
                            id="devis_return_date" placeholder="JJ/MM/AAAA" required>
                        <i class="fa fa-calendar input-date-icon" target="#devis_return_date"
                            style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="row mt-3">
                    <label for="nbpassagers2" class="mb-0 col-2"><strong>Nbres passagers</strong></label>
                    <div class="listtotalnbpassagers d-flex flex-column col-5 w-100 pl-0">
                        <div class="align-items-center" style="border-radius:5px">
                            <button class="btn d-flex" id="nbpassagers2" type="button"
                                style="font-size: 14px;height:37.5px;background-color: white;color: #005eb8;width: 300px;">
                                <img src="{{ asset('sys/clients.svg') }}" width="20" height="20"
                                    style="fill: #005eb8 !important;">
                                <span id="nbprsn2">1 Adulte - 0 Enfant - 0 Bébé (total : 1)</span>
                            </button>
                            <div class="passagersdropdown" id="passagersdropdown2"
                                style="display:none;width: 300px;">
                                <div class="card card-body"
                                    style="width:300px;position: absolute; z-index: 5;">

                                    <h4 style="color: #22366b">Passager</h4>
                                    <div class="modal-body">
                                        <div class="input-group">
                                            <!-- Adulte -->
                                            <div class="d_menu d-flex align-items-center">
                                                <img src="{{ asset('front/img/moins.png') }}" style="height: 15px"
                                                    class="img_ps fa-minus-circle" id="removeadulat2">
                                                <span id="adult_m2" class="span_num mx-1" style="font-size: 22px">1
                                                </span>
                                                <input type="hidden" name="nb_passagers_adultes"
                                                    id="nb_passagers_adultes2" value="1">
                                                <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                    class="img_ps fa-plus-circle" id="addadulat2">
                                                Adulte
                                            </div>

                                            <!-- Enfant -->
                                            <div class="d_menu d-flex align-items-center">
                                                <img src="{{ asset('front/img/moins.png') }}" style="height: 15px"
                                                    class="img_ps fa-minus-circle" id="removeenfant2">
                                                <span id="enfant_m2" class="span_num mx-1"
                                                    style="font-size: 22px !important;">0</span>
                                                <input type="hidden" name="nb_passagers_childs"
                                                    id="nb_passagers_childs2" value="0">
                                                <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                    class="img_ps fa-plus-circle" id="addenfant2">
                                                Enfant (2-11 ans)
                                            </div>

                                            <!-- Bébé -->
                                            <div class="d_menu d-flex align-items-center">
                                                <img src="{{ asset('front/img/moins.png') }}" style="height: 15px"
                                                    class="img_ps fa-minus-circle" id="removebebe2">
                                                <span id="bebe_m2" class="span_num mx-1" style="font-size: 22px">0
                                                </span>
                                                <input type="hidden" name="nb_passagers_babies"
                                                    id="nb_passagers_babies2" value="0">
                                                <img src="{{ asset('front/img/plus.png') }}" style="height: 15px"
                                                    class="img_ps fa-plus-circle" id="addbebe2">
                                                Bébé (< 2 ans) </div>

                                                    <div id="message_div2"
                                                        style="color: red; font-size: 12px; padding-left: 15px;">
                                                    </div>
                                                    <div class="text-right">
                                                        <button class="btn btn-sm btn-primary closepassagersmodal2"
                                                            type="button">Valider
                                                        </button>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="nb_passagers_adultes" id="nb_passagers_adultes2" value="1">
                    <input type="hidden" name="nb_passagers_childs" id="nb_passagers_childs2" value="0">
                    <input type="hidden" name="nb_passagers_babies" id="nb_passagers_babies2" value="0">

                    <div class="row align-items-center mt-3">
                        <label for="proposed_price" class="mb-0 col-2"><strong>Prix proposé</strong></label>
                        <input class="form-control col-5" type="number" name="proposed_price" id="proposed_price"
                            placeholder="Prix (€)" required>
                    </div>


                    <div class="row mt-3">
                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <p class="text-danger mb-0">{{ $error }}</p>
                            @endforeach
                        @endif
                    </div>
            </form>
        </div>
        <div class="card-footer d-flex justify-content-center align-items-center position-relative">
            <button type="button" id="sendDevisSms"
                class="btn-save-devis btn btn-primary mt-3 px-3 py-2 rounded-3 scroll-save-button"
                style="border-radius: 11px; margin-right: 149px;">Envoyer SMS
            </button>
        </div>
        <div id="message-container"></div>

    </div>
</div>
