<div class="card my-3">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between accordion-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <img src="{{ asset('sys/fiche.png') }}" alt="Icone Demande"
                style="width: 60px; height: 60px; margin-right: 10px;">
            Infos client
        </h6>
    </div>
    <div class="accordion-content">
        <div class="card-body">
            <div class="row align-items-center mt-3">
                <label for="phone_number" class="mb-0 col-2"><strong>Téléphone</strong></label>

                <div class="col-2">
                    <input type="text" name="phoneNumber" id="phone_number" class="form-control"
                        style="width: 244px;" value="{{ $clients[0]->phoneNumber ?? '' }}" />
                </div>

                <div id="output" class="mt-3"></div>

                <input type="hidden" name="phoneNumberE164" id="phoneNumberE164">
            </div>

            <div id="client_info" class="mt-3"></div>
        </div>


        <div class="card-footer d-flex justify-content-center align-items-center position-relative">
            <button type="button" id="enregistrerClient"
                class="btn btn-primary mt-3 px-3 py-2 rounded-3 scroll-save-button"
                style="border-radius: 11px; margin-right: 149px;">Enregistrer
            </button>
        </div>

    </div>
    <div id="infoclient-message"></div>
</div>
