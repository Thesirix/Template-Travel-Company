@props(['countries', 'volGroup' => null])
<div class="row align-items-end">
    <div class="col-md-3 mt-3">
        <label for="name">
            Nom
        </label>
        <input type="text" class="form-control" id="name" name="name"
               value="@isset($volGroup) {{$volGroup->name}} @else {{old('name')}} @endisset">
    </div>
    <div class="col-md-3 mt-3">
        <label for="country_id">
            Pays
        </label>
        <select class="form-control" name="country_id" id="country_id">
            @foreach($countries as $country)
                <option value="{{$country->id}}"
                        @if(isset($volGroup) && $volGroup->country_id== $country->id) selected @endif>
                    {{$country->nom}}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mt-3">
        <label for="type">
            Type
        </label>
        <select class="form-control" name="type" id="type">
            <option value="{{\App\Enums\GroupType::ROUND_TRIP->value}}"
                    @if(isset($volGroup) && $volGroup->type== \App\Enums\GroupType::ROUND_TRIP->value) selected @endif>
                Aller-retour
            </option>
            <option value="{{\App\Enums\GroupType::MULTI_SEGMENT_OPTIONAL->value}}"
                    @if(isset($volGroup) && $volGroup->type== \App\Enums\GroupType::MULTI_SEGMENT_OPTIONAL->value) selected @endif>
                Segments au choix
            </option>
            <option value="{{\App\Enums\GroupType::MULTI_SEGMENT_REQUIRED->value}}"
                    @if(isset($volGroup) && $volGroup->type== \App\Enums\GroupType::MULTI_SEGMENT_REQUIRED->value) selected @endif>
                Tous les segments obligatoires
            </option>
        </select>
    </div>
    <div class="col-md-3 mt-3">
        <label for="same_itineraire">
            Même itinéraire obligatoire
        </label>
        <select class="form-control" name="same_itineraire" id="same_itineraire">
            <option value="1"
                    @if(isset($volGroup) && $volGroup->same_itineraire== 1) selected @endif>
                Oui
            </option>
            <option value="0"
                    @if(isset($volGroup) && $volGroup->same_itineraire== 0) selected @endif>
                Non
            </option>
        </select>
    </div>
    <div class="col-md-3 mt-3">
        <label for="is_active">
            Actif
        </label>
        <select class="form-control" name="is_active" id="is_active">
            <option value="1"
                    @if(isset($volGroup) && $volGroup->is_active== 1) selected @endif>
                Oui
            </option>
            <option value="0"
                    @if(isset($volGroup) && $volGroup->is_active== 0) selected @endif>
                Non
            </option>
        </select>
    </div>
    <div class="col-md-3 mt-3">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</div>
