@props(['destination', 'vol' => null])
@php
    // Colonnes prix_achat_* / prix_vente_* des vols
    $categories = ['adult' => 'adulte', 'enfant' => 'enfant', 'bebe' => 'bébé'];
@endphp
<div class="card my-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-hand-holding-usd"></i>
            Prix par passager
        </h4>
    </div>
    <div class="card-body row">
        @foreach(['achat' => "Prix d'achat", 'vente' => 'Prix de vente'] as $kind => $title)
            @foreach($categories as $key => $label)
                <div class="col-md-3 mb-3">
                    <label for="prix_{{ $kind }}_{{ $key }}">{{ $title }} {{ $label }}</label>
                    <div class="input-group">
                        <input id="prix_{{ $kind }}_{{ $key }}" type="number" class="form-control" step=".01" min="0" required
                               name="prix_{{ $kind }}_{{ $key }}" value="{{ old('prix_'.$kind.'_'.$key, $vol['prix_'.$kind.'_'.$key] ?? '') }}">
                        <div class="input-group-append"><span class="input-group-text">€</span></div>
                    </div>
                </div>
            @endforeach
        @endforeach
        <div class="col-md-3 mb-3">
            <label for="total_a_payer">Acompte à la réservation (par personne)</label>
            <div class="input-group">
                <input id="total_a_payer" type="number" class="form-control" name="total_a_payer" step=".1" min="0"
                       value="{{ old('total_a_payer', $vol->total_a_payer ?? 150) }}">
                <div class="input-group-append"><span class="input-group-text">€</span></div>
            </div>
        </div>
    </div>
</div>
