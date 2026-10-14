<div class="card my-3">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between accordion-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <img src="{{ asset('sys/confirmer.png') }}" alt="Icone Demande"
                style="width: 50px; height: 50px; margin-right: 15px; margin-left: 5px; margin-top: 5px; margin-bottom: 5px;">
            Description
        </h6>
    </div>
    <div class="accordion-content">
        <div class="card-body">
            <div class="row mb-3">

                <p class="col-xl-2 col-lg-1"><strong>Provenance</strong></p>
                <div class="col-xl-10 col-lg-11">

                    <div class="d-flex flex-wrap mb-3">
                        <div class="form-check mb-2" style="margin-right: 20px;">
                            <input class="form-check-input" type="checkbox" name="provenance[]" value="Comptoir"
                                id="Comptoir">
                            <label class="form-check-label" for="Comptoir">Comptoir</label>
                        </div>
                        <div class="form-check mb-2" style="margin-right: 20px;">
                            <input class="form-check-input" type="checkbox" name="provenance[]" value="Appel"
                                id="Appel">
                            <label class="form-check-label" for="Appel">Appel</label>
                        </div>
                        <div class="form-check mb-2" style="margin-right: 20px;">
                            <input class="form-check-input" type="checkbox" name="provenance[]" value="Internet"
                                id="Internet">
                            <label class="form-check-label" for="Internet">Internet</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="provenance[]" value="Fidèle"
                                id="Fidèle">
                            <label class="form-check-label" for="Fidèle">Fidèle</label>
                        </div>
                    </div>
                </div>

                <p class="col-xl-2 col-lg-1"><strong>Prestation</strong></p>
                <div class="col-xl-10 col-lg-11">

                    <div class="d-flex flex-wrap">

                        <div class="form-check mb-2" style="margin-right: 20px;">
                            <input class="form-check-input" type="checkbox" name="options[]" value="Devis"
                                id="Devis">
                            <label class="form-check-label" for="Devis">Devis</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="options[]" value="Vente"
                                id="Vente">
                            <label class="form-check-label" for="Vente">Vente</label>

                            <input type="hidden" id="is_vente" name="is_vente" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="client_vol_estimate_id" name="client_vol_estimate_id"
                value="{{ $clientVolEstimate->id ?? '' }}">
                <div class="row align-items-center mt-3">
                    <label for="marge" class="mb-0 col-2"><strong>Marge appliquée</strong></label>
                    <input class="form-control col-5" type="number" name="marge" id="marge"
                        placeholder="Marge (€)" required>
                </div>

            <div class="row mb-3 mt-3">
                <label for="comment" class="form-label col-xl-2 col-lg-1"><strong>Commentaire</strong></label>
                <textarea class="form-control col-xl-5 col-lg-11" id="comment" name="comment"></textarea>
            </div>

        </div>
        <div class="card-footer d-flex align-items-center position-relative">
            <button type="button" id="save_description"
                class="btn-save-description btn btn-primary mt-3 px-3 py-2 mx-auto" style="border-radius: 11px;">
                Enregistrer
            </button>
            <button type="button" class="reset-button btn btn-warning px-3 py-2 mx-3"
                style="border-radius: 11px;">Réinitialiser
            </button>
        </div>
        <div id="description-message-container"></div>
    </div>
</div>
