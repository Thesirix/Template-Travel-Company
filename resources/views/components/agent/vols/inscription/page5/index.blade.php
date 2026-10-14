@props(['vol', 'volRetour', 'typeVoyage', 'destination'])
<div id="page5" style="margin-top:20vh;display: none">
    <div class="d-flex flex-wrap justify-content-center">
        <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef;width: 600px">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h6 class="m-0 font-weight-bold text-light">
                        Recapitulatif
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <table style="font-size: 17px">
                    <tr>
                        <td style="width: 30% " class="text-primary font-weight-bold pt-3">Itineraire</td>
                        <td class="pt-3">
                            {{$vol->itineraire}}
                        </td>
                    </tr>
                    <tr>
                        <td class="pt-3 font-weight-bold text-primary">Compagnie</td>
                        <td class="pt-3">{{$vol->company->nom}}</td>
                    </tr>
                    @if($typeVoyage != 'retour_simple')
                        <tr>
                            <td class="pt-3 text-primary font-weight-bold">Date Aller</td>
                            <td class="pt-3">
                                {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                            </td>
                        </tr>
                    @endif
                    @if($typeVoyage != 'aller_simple')
                        <tr>
                            <td class="pt-3 text-primary font-weight-bold">Date retour</td>
                            <td class="pt-3">
                                @if($typeVoyage=='aller_retour_same_vol')
                                    {{\Carbon\Carbon::parse($vol->date_retour)->format("d/m/Y")}}
                                @elseif($typeVoyage=='aller_retour_diffrent_vol')
                                    {{\Carbon\Carbon::parse($volRetour->date_aller)->format("d/m/Y")}}
                                @elseif($typeVoyage=='retour_simple')
                                    {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Bagages</td>
                        <td class="pt-3">
                            {{$vol->soute_num}} x {{$vol->bagage}} kgs + {{$vol->bg_hand}} Kgs
                        </td>
                    </tr>
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Passagers</td>
                        <td class="pt-3 Recapitulatif_passagers">
                        </td>
                    </tr>
                    
                    @if($destination == 'one')
                        <tr>
                            <td class="pt-3 text-primary font-weight-bold">Assurances</td>
                            <td class="pt-3 assuences_total"></td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="card-footer pb-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary page-validation-check"
                        pageNum="5" validationNum="2"
                        id="page5-validation-check1" next="page5-validation-check2">Valider
                </button>
            </div>

        </div>
        <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef;width: 600px;">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h6 class="m-0 font-weight-bold text-light">
                        Paiement
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <table style="font-size: 17px">
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Total à payer</td>
                        <td class="pt-3 devis-prix-total"></td>
                    </tr>
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Paiement à l'inscription</td>
                        <td class="pt-3 paiement_inscription"></td>
                    </tr>
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Derniere limite de paiement</td>
                        <td class="pt-3 ">{{\Illuminate\Support\Carbon::parse($vol->escaliers->where('type',0)->where('num',1)->first()->date_depart)->subDays(45)->format('d/m/Y')}}</td>
                    </tr>
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Numero Facture</td>
                        <td class="pt-3">
                            <input type="number"  id="num_acompte" required
                                   name="num_acompte" class="form-control" min="400000" max="600000"
                                   style="width: 200px">
                        </td>
                    </tr>
                    <tr>
                        <td class="pt-3 text-primary font-weight-bold">Montant Acompte</td>
                        <td class="pt-3">
                            <input type="number" min="300" id="montant_acompte" required
                                   name="montant_acompte"
                                   class="form-control"
                                   style="width: 200px">
                        </td>
                    </tr>

                </table>
            </div>
            <div class="card-footer pb-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary page-validation-check"
                        pageNum="5" validationNum="2"
                        id="page5-validation-check2">Valider
                </button>
            </div>
        </div>
    </div>
</div>
