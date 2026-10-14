<!-- Modal admin de gestion des congés (modif)-->

<div class="modal fade" id="adminManageModal" tabindex="-1" aria-labelledby="adminManageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adminManageForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminManageModalLabel">Gestion du congé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label"><b>Agent :</b></label>
                        <div id="adminAgentName" class="fw-bold"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><b>Raison du congé :</b></label>
                        <div id="adminReasonConge" class="fw-bold"></div>
                    </div>

                    <div class="mb-2">
                        <label for="adminStartDate"><b>Date de début</b></label>
                        <input type="date" class="form-control" id="adminStartDate" name="start_date">
                    </div>

                    <div class="mb-2">
                        <label for="adminEndDate"><b>Date de fin</b></label>
                        <input type="date" class="form-control" id="adminEndDate" name="end_date">
                    </div>

                    <div class="mb-2" id="adminStatusContainer">
                        <label for="adminStatus"><b>Statut</b></label>
                        <select class="form-control" id="adminStatus" name="status">
                            <option value="approved">Approuvé</option>
                            <option value="rejected">Refusé</option>
                            <option value="maladie" hidden>Maladie</option>


                        </select>
                    </div>


                    {{-- ---------------type-------------------------------- --}}


                    <div class="mb-3" id="adminCongeTypesContainer">
                        <label class="form-label"><b>Jours par type</b></label>
                        <div id="adminCongeTypes_edit" class="d-flex flex-wrap">

                            <!-- Type Congé -->
                            <div class="mr-3 mb-2">
                                <label>Congé</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('conge',-1)">-</button>
                                    <input type="text" id="admin_conge_edit" name="admin_conge_edit"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('conge',1)">+</button>
                                </div>
                            </div>

                            <!-- Type Repos -->
                            <div class="mr-3 mb-2">
                                <label>Repos</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('repos',-1)">-</button>
                                    <input type="text" id="admin_repos_edit" name="admin_repos_edit"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('repos',1)">+</button>
                                </div>
                            </div>

                            <!-- Type Férié -->
                            <div class="mr-3 mb-2">
                                <label>Férié</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('ferie',-1)">-</button>
                                    <input type="text" id="admin_ferie_edit" name="admin_ferie_edit"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('ferie',1)">+</button>
                                </div>
                            </div>

                            <!-- Type Autre -->
                            <div class="mr-3 mb-2">
                                <label>Autre</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('autre',-1)">-</button>
                                    <input type="text" id="admin_autre_edit" name="admin_autre_edit"
                                        class="form-control form-control-sm text-center" value="0" readonly
                                        style="width:40px;">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="updateDaysAdminEdit('autre',1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- --------------------------------------------------------------- --}}

                    <div class="mb-2" id="adminReasonContainer" style="display: none;">
                        <label for="adminReason" class="form-label fw-bold">Motif du refus</label>
                        <textarea class="form-control" id="adminReason" name="reason" rows="3"
                            placeholder="Indiquez la raison du refus ici..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="adminDeleteBtn">Supprimer</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>

                <div id="form-error-msg-admin-update" class="text-danger fw-bold"
                    style="white-space: pre-line; display:none;"></div>

            </form>
        </div>
    </div>
</div>

<div id="statusMessage"
    style="position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 10px; z-index: 1000; display: none;">
</div>
