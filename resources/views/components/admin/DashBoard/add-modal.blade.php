<!---------------------------- modal Ajout Congé Admin----------------------------------------------------------------- -->

<div class="modal fade" id="modalAjoutCongeAdmin" tabindex="-1" aria-labelledby="modalAjoutCongeAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAjoutCongeAdmin">
            <div class="modal-content">


                <div class="modal-header">
                    <h5 class="modal-title">Créer un nouveau congé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>


                <div class="modal-body">

                    <!-- Agence -->
                    <div class="mb-3">
                        <label for="agenceSelect" class="form-label"><b>Agence</b></label>
                        <select class="form-select" id="agenceSelect">
                            <option selected disabled>Choisissez une agence</option>
                        </select>
                    </div>

                    <!-- Agent -->
                    <div class="mb-3">
                        <label for="agentSelect" class="form-label"><b>Agent</b></label>
                        <select class="form-select" id="agentSelect">
                            <option selected disabled>Choisissez un agent</option>
                        </select>
                    </div>

                    <!-- Titre -->
                    <div class="mb-3">
                        <label for="titleConge" class="form-label"><b>Nom du congé</b></label>
                        <input type="text" class="form-control" id="titleConge">
                    </div>

                    <!-- Dates -->
                    <div class="mb-3">
                        <label for="startDateConge" class="form-label"><b>Date de début</b></label>
                        <input type="date" class="form-control" id="startDateConge">
                    </div>

                    <div class="mb-3">
                        <label for="endDateConge" class="form-label"><b>Date de fin</b></label>
                        <input type="date" class="form-control" id="endDateConge">
                    </div>

                    <!-- Jours par type (compteurs ±) -->
                    <div class="mb-3">
                        <label class="form-label"><b>Jours par type</b></label>

                        <div id="adminCongeTypes" class="d-flex flex-wrap">

                            <!-- Généré manuellement pour garder les id -->
                            <div class="mr-3 mb-2">
                                <label>Congé</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('conge',-1)">-</button>
                                    <input type="text" id="admin_conge"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('conge',1)">+</button>
                                </div>
                            </div>

                            <div class="mr-3 mb-2">
                                <label>Repos</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('repos',-1)">-</button>
                                    <input type="text" id="admin_repos"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('repos',1)">+</button>
                                </div>
                            </div>

                            <div class="mr-3 mb-2">
                                <label>Férié</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('ferie',-1)">-</button>
                                    <input type="text" id="admin_ferie"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('ferie',1)">+</button>
                                </div>
                            </div>

                            <div class="mr-3 mb-2">
                                <label>Autre</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('autre',-1)">-</button>
                                    <input type="text" id="admin_autre"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdmin('autre',1)">+</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Bouton & bloc Congé Maladie -->
                    <div class="mr-3 mb-2 d-flex align-items-center">
                        <button id="btnMaladieToggleAdmin" type="button"
                            class="btn btn-danger btn-sm"><b>CM</b></button>
                    </div>

                    <div id="blocMaladieDatesAdmin" style="display:none;margin-top:1rem;">
                        <p class="text-danger font-weight-bold mb-2">Poser un congé maladie :</p>
                        <div class="form-group mb-2">
                            <label for="maladie_fromAdmin"><b>Date de début</b></label>
                            <input type="date" id="maladie_fromAdmin" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="maladie_toAdmin"><b>Date de fin</b></label>
                            <input type="date" id="maladie_toAdmin" class="form-control">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Créer le congé</button>
                </div>

                <div id="form-error-msg-admin" class="text-danger fw-bold"
                    style="white-space: pre-line; display:none;"></div>

            </div>
        </form>
    </div>
</div>
