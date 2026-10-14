@props(['groupId'])
<div class="modal fade delete-passport-passenger-modal" id="delete-passport-passenger-modal" tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('agent.vols.reservations.passport.destroy', $groupId) }}"
                  id="delete-passport-passenger-form" class="modal-body" style="font-size: 20px">
                @csrf
                <input type="hidden" id="passenger_id" name="passenger_id">
                <input type="hidden" id="file_type" name="file_type" value="passport">
                <div class="d-flex align-items-center">
                    <img src="{{asset('sys/warning.svg')}}" style="height: 40px">
                    <h5 class="ml-3">Supprimer cette pièce jointe du passager ?</h5>
                </div>
                <div class="text-right mt-5">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>
