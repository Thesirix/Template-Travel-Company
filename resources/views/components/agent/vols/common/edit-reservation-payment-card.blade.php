@props(['flightsGroup', 'acompte', 'loop', 'reservationId', 'reservationTotalPrice', 'reservationPayedAmount'])
<div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
    <div class="card-header pb-3 pt-0">
        <div style="height: 30px" class="bg-primary text-center">
            <h6 class="m-0 font-weight-bold text-light">
                Paiement
            </h6>
        </div>
    </div>
    <form method="post" class="card-body submit-form"
          action="{{route('agent.vols.paiements.update', $flightsGroup->id)}}">
        @csrf
        <input type="hidden" name="acompte_id" value="{{$acompte->id}}">
        <div class=" px-4 py-3  "
             style="font-size: 18px;">
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" acompte_id="{{$acompte->id}}"
                        class="btn btn-primary update_payment">
                    Modifier
                </button>
                <button acompte_id="{{$acompte->id}}"
                        class="btn btn-primary save_payment{{$acompte->id}}"
                        style="display: none">
                    Enregistrer
                </button>
                @if($loop->index !=0)
                    <button class="btn btn-danger delete_payment" acompte_id="{{$acompte->id}}"
                            type="button">Supprimer
                    </button>
                @endif
            </div>
            <table class="custum_table">
                <tr>
                    <td>Montant Total Dossier</td>
                    <td>{{$reservationTotalPrice}} €</td>
                </tr>
                <tr>
                    <td>Date Acompte</td>
                    <td style="display: none" class="showacompte{{$acompte->id}}">
                        <input type="date" class="form-control "
                               name="created_at"
                               value="{{\Carbon\Carbon::parse($acompte->created_at)->format('Y-m-d')}}">
                    </td>
                    <td class="hideacompte{{$acompte->id}}">
                        {{\Carbon\Carbon::parse($acompte->created_at)->format('d/m/Y')}}
                    </td>
                </tr>
                <tr>
                    <td>{{ $loop->first ? 'Numero Facture' : 'N Acompte'}}</td>
                    <td style="display: none" class="showacompte{{$acompte->id}}">
                        <input type="number" class="form-control "
                               name="n_acompte"
                               value="{{$acompte->n_acompte}}">
                    </td>

                    <td class="hideacompte{{$acompte->id}}">{{$acompte->n_acompte}}</td>
                </tr>
                <tr>
                    <td>Montant</td>
                    <td style="display: none" class="showacompte{{$acompte->id}}">
                        <input type="number" class="form-control " name="montant"
                               min="1"
                               step="1"
                               onkeyup="updateRemainingAmount({{$reservationTotalPrice}},{{$reservationPayedAmount}},{{$acompte->montant}},this)"
                               max="{{$reservationTotalPrice-$reservationPayedAmount+$acompte->montant}}"
                               value="{{$acompte->montant}}">
                    </td>
                    <td class="hideacompte{{$acompte->id}}">{{$acompte->montant}} €</td>
                </tr>
                <tr>
                    <td>Reste à payer</td>
                    <td class="remaining-amount">{{$reservationTotalPrice-$reservationPayedAmount}}
                        €
                    </td>
                </tr>
            </table>
            @if($loop->last)
                <div class="text-center">
                    <button class="btn btn-primary mt-3  add-new-payment" type="button">
                        Ajouter
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>

<div class="modal fade" id="warning_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="modal-body submit-form" method="post"
                  action="{{route('agent.vols.paiements.destroy', $flightsGroup->id)}}">
                @csrf
                <input type="hidden" name="delete_acompte_id" id="delete_acompte_id">
                <input type="hidden" name="res_id" value="{{$reservationId}}">
                <div class="d-flex align-items-center" style="font-size: 16px">
                    <img src="{{asset('sys/warning.svg')}}" style="height: 60px">
                    <p class="ml-3 mb-0">Voulez-vous supprimer ce payment</p>
                </div>
                <div class="text-right">
                    <button class="btn btn-danger mt-4 ">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>
