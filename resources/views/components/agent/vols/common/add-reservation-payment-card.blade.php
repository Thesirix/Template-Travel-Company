@props(['flightsGroup', 'reservationId', 'reservationTotalPrice', 'reservationPayedAmount'])
<div class="card mb-3" id="add-payement-section" style="display: none;width: 480px">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            Paiement
        </h6>
    </div>
    <form method="post" class="card-body submit-form"
          action="{{route('agent.vols.paiements.store', $flightsGroup->id)}}">
        @csrf
        <input type="hidden" name="reservation_id" value="{{$reservationId}}">
        <div class=" px-4 py-3 mb-5 " style="font-size: 18px">
            <table class="custum_table">
                <tr>
                    <td>Montant Total Dossier</td>
                    <td>{{$reservationTotalPrice}} €</td>
                </tr>
                <tr>
                    <td>Date Acompte</td>
                    <td><input type="text" value="{{date('d/m/Y')}}" class="form-control"
                               readonly="readonly"></td>
                </tr>
                <tr>
                    <td>N Acompte</td>
                    <td><input type="number" class="form-control" name="n_acompte"></td>
                </tr>
                <tr>
                    <td>Montant</td>
                    <td><input type="number" class="form-control" name="montant" id="montant"
                               min="1"
                               step="1"
                               onkeyup="updateRemainingAmount({{$reservationTotalPrice}},{{$reservationPayedAmount}},0,this)"
                               max="{{$reservationTotalPrice-$reservationPayedAmount}}"></td>
                </tr>
                <tr>
                    <td>Reste à payer</td>
                    <td class="remaining-amount" id="test">{{$reservationTotalPrice-$reservationPayedAmount}}
                        €
                    </td>
                </tr>
                <tr>
                    <td>Observation</td>
                    <td><textarea name="observation" class="form-control"></textarea></td>
                </tr>
            </table>
            <div class="text-center">
                <button class="btn btn-primary mt-3">Enregistrer</button>
            </div>
        </div>
    </form>
</div>
