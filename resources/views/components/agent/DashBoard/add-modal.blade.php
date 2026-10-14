<div class="modal fade" id="addeventmodal" tabindex="-1" aria-labelledby="addeventmodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border: none !important;">
                <h5 class="modal-title text-primary m-0 font-weight-bold" id="addeventmodalLabel">Demande de congé
                </h5>



                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-3">
                <div class="row align-items-center">
                    <label for="modaltitle" class="col-2 mb-3">Titre</label>
                    <input type="text" name="modaltitle" id="modaltitle" class="form-control col-6 mb-3">
                </div>


                <div class="row align-items-center">
                    <label for="modalfrom" class="col-2 mb-2">Début</label>
                    <input type="date" name="modalfrom" id="modalfrom" class="form-control col-6 mb-2">
                </div>
                <div class="row align-items-center">
                    <label for="modalto" class="col-2 mb-2">Fin</label>
                    <input type="date" name="modalto" id="modalto" class="form-control col-6 mb-2" min="">
                </div>


                {{-- ------bouton plus moins------------- --}}

                <div class="row align-items-center mb-3">
                    <label class="col-2">Type</label>
                    <div class="col-10 d-flex flex-wrap" id="congeTypes">

                        <div class="mr-3 mb-2">
                            <label>Congé</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('conge', -1)">-</button>
                                </div>
                                <input type="text" id="conge" name="conge"
                                    class="form-control form-control-sm text-center" value="0" readonly
                                    style="width: 40px;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('conge', 1)">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="mr-3 mb-2">
                            <label>Repos</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('repos', -1)">-</button>
                                </div>
                                <input type="text" id="repos" name="repos"
                                    class="form-control form-control-sm text-center" value="0" readonly
                                    style="width: 40px;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('repos', 1)">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="mr-3 mb-2">
                            <label>Férié</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('ferie', -1)">-</button>
                                </div>
                                <input type="text" id="ferie" name="ferie"
                                    class="form-control form-control-sm text-center" value="0" readonly
                                    style="width: 40px;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('ferie', 1)">+</button>
                                </div>
                            </div>
                        </div>


                        <div class="mr-3 mb-2">
                            <label>Autre</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('autre', -1)">-</button>
                                </div>
                                <input type="text" id="autre" name="autre"
                                    class="form-control form-control-sm text-center" value="0" readonly
                                    style="width: 40px;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDays('autre', 1)">+</button>
                                </div>
                            </div>
                        </div>

                    </div>


                    {{-- -----------------------------------------------------Maladie--------------------------- --}}


                </div>

                <!-- BOUTON "CM" -->
                <div class="mr-3 mb-2 d-flex align-items-center">
                    <button id="btnMaladieToggle" class="btn btn-danger btn-sm" type="button"><b>CM</b></button>
                </div>


                <div id="blocMaladieDates" style="display: none; margin-top: 1rem;">
                    <p class="text-danger font-weight-bold">Poser un congé maladie :</p>
                    <div class="form-group">
                        <label for="maladie_from">Date de début</label>
                        <input type="date" id="maladie_from" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="maladie_to">Date de fin</label>
                        <input type="date" id="maladie_to" class="form-control">
                    </div>
                </div>


            </div>
            <p id="form-error-msg" style="color: red; display: none;"></p>

            <div class="modal-footer pt-0" style="border: none !important;">


                <button type="button" class="btn btn-primary addeventbutton" id="addeventbutton">Ajouter</button>
            </div>
        </div>
    </div>
</div>
