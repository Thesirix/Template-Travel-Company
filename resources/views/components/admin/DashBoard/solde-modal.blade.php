{{-- ------------------------------modal edition des solde -----------  --}}


<div class="modal fade" id="agentSoldeModal" tabindex="-1" aria-labelledby="agentSoldeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formSoldeAgent">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agentSoldeModalLabel">Solde de l’agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="soldeAgentId" name="agent_id">

                    <!-- Agent -->
                    <div class="mb-3">
                        <label class="form-label"><b>Agent</b></label>
                        <div id="soldeAgentName" class="fw-bold"></div>
                    </div>

                    <!-- Congé -->
                    <div class="mb-3">
                        <label class="form-label"><b>Congé</b></label>
                        <div id="solde_conge_view"></div> <!-- Texte simple -->
                        <input type="number" step="0.01" class="form-control d-none" id="solde_conge"
                            name="solde_conge">
                        <!-- Input caché -->
                    </div>

                    <!-- Repos -->
                    <div class="mb-3">
                        <label class="form-label"><b>Repos</b></label>
                        <div id="solde_repos_view"></div>
                        <input type="number" step="0.01" class="form-control d-none" id="solde_repos"
                            name="solde_repos">
                    </div>

                    <!-- Férié -->
                    <div class="mb-3">
                        <label class="form-label"><b>Férié</b></label>
                        <div id="solde_ferie_view"></div>
                        <input type="number" step="0.01" class="form-control d-none" id="solde_ferie"
                            name="solde_ferie">
                    </div>

                    <!-- Autre -->
                    <div class="mb-3">
                        <label class="form-label"><b>Autre</b></label>
                        <div id="solde_autre_view"></div>
                        <input type="number" step="0.01" class="form-control d-none" id="solde_autre"
                            name="solde_autre">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><b>Jours travaillés depuis le 1er janvier</b></label>


                        <div id="presenceCounterYear" style="margin-bottom:10px; ">

                        </div>

                    </div>

                    <div id="form-error-msg-solde" class="text-danger fw-bold"
                        style="white-space: pre-line; display:none;"></div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btnEditSolde">✏️ Modifier</button>
                        <button type="button" class="btn btn-outline-secondary d-none"
                            id="btnCancelEdit">Annuler</button>
                    </div>
                    <button type="submit" class="btn btn-primary d-none" id="btnSaveSolde">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>
