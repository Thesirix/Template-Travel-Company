 <!-- Modal de refus avec motif -->

 <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <form id="rejectForm">
                 <div class="modal-header">
                     <h5 class="modal-title">Refuser le congé</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                 </div>
                 <div class="modal-body">
                     <div class="form-group">
                         <label for="rejectionReason">Motif du refus</label>
                         <textarea class="form-control" id="rejectionReason" name="rejectionReason" rows="3"></textarea>
                     </div>
                 </div>
                 <div class="modal-footer">

                     <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                 </div>
             </form>
         </div>
     </div>
 </div>
