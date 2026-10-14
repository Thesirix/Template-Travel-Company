<!-- Modal de confirmation -->

<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Souhaitez-vous approuver ou refuser ce congé ?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">

                <div class="mb-2">
                    <label class="form-label"><b>Agent :</b></label>
                    <div id="approveAgentName" class="fw-bold"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label"><b>Raison du congé :</b></label>
                    <div id="approveReasonConge" class="fw-bold"></div>
                </div>
                <p><b>Vous pouvez approuver ou refuser cette demande.</b></p>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-danger" id="openRejectModal">Refuser</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Approuver</button>
            </div>
        </div>
    </div>
</div>
