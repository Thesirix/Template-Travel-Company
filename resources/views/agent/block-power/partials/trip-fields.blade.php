{{-- Trajet, places, classe et prix : communs aux options et aux demandes. Attend $value(champ), $date(champ) et les listes du catalogue. --}}
@foreach(['country' => ['Pays', $countries], 'company' => ['Compagnie', $companies], 'departure_city' => ['Ville de départ', $departureCities], 'arrival_city' => ["Ville d'arrivée", $arrivalCities]] as $field => [$label, $items])
    <div class="form-group col-md-3">
        <label for="{{ $field }}">{{ $label }}</label>
        <select name="{{ $field }}" id="{{ $field }}" class="form-control" @required($field !== 'company')>
            @if($field === 'company')<option value="">—</option>@endif
            @foreach($items->when($value($field) && ! $items->contains($value($field)), fn ($list) => $list->prepend($value($field))) as $item)
                <option @selected($value($field) === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
@endforeach
<div class="form-group col-md-3">
    <label for="date_aller">Date aller</label>
    <input type="date" name="date_aller" id="date_aller" class="form-control" value="{{ $date('date_aller') }}">
</div>
<div class="form-group col-md-3">
    <label for="date_retour">Date retour</label>
    <input type="date" name="date_retour" id="date_retour" class="form-control" value="{{ $date('date_retour') }}">
</div>
<div class="form-group col-md-2">
    <label for="seats">Places</label>
    <input type="number" name="seats" id="seats" class="form-control" required min="1" max="500" value="{{ $value('seats') }}">
</div>
<div class="form-group col-md-2">
    <label for="cabin_class">Classe</label>
    <input type="text" name="cabin_class" id="cabin_class" class="form-control" required maxlength="50" value="{{ $value('cabin_class') ?? 'Économique' }}">
</div>
<div class="form-group col-md-2">
    <label for="price">Prix (€)</label>
    <input type="number" name="price" id="price" class="form-control" required min="0" step="0.01" value="{{ $value('price') }}">
</div>
