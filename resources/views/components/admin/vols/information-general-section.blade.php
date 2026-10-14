@props(['villes', 'conditions', 'destination', 'operation', 'groups', 'vol' => null])
<div class="card mt-5">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-map"></i>
            Informations general
        </h4>
    </div>
    <div class="card-body row">
        
            <div class="col-md-3 {{ $errors->has('group_id') ? ' has-error' : '' }}">
                <label for="type_voyage">Group</label>
                    <select name="group_id" id="group_id" class="form-control">
                        @foreach($groups as $group)
                            <option value="{{$group->id}}"
                                    @if(isset($vol) && $vol->vol_group_id== $group->id) selected @endif>
                                {{$group->country->nom}} {{$group->name}}
                            </option>
                        @endforeach
                    </select>
                @if ($errors->has('group_id'))
                    <p class="help-block">
                        <strong>{{ $errors->first('group_id') }}</strong>
                    </p>
                @endif
            </div>

            <div class="col-md-3 {{ $errors->has('type_voyage') ? ' has-error' : '' }}">
                <label for="type_voyage">Type de voyage</label>
                @if(isset($vol))
                    <input type="hidden" name="type_voyage" value="{{$vol->type_voyage}}">
                    <input type="text" readonly="readonly" value="{{str_replace('_',' ',$vol->type_voyage)}}"
                           class="form-control">
                @else
                    <select name="type_voyage" id="type_voyage" class="form-control">
                        <option value="aller_retour"
                                @if(isset($vol) && $vol->type_voyage== 'aller_retour') selected @endif>
                            Aller Retour
                        </option>
                        <option value="aller_simple"
                                @if(isset($vol) && $vol->type_voyage== 'aller_simple') selected @endif>
                            Aller Simple
                        </option>
                        <option value="retour_simple"
                                @if(isset($vol) && $vol->type_voyage== 'retour_simple') selected @endif>Retour Simple
                        </option>
                    </select>
                @endif
                @if ($errors->has('type_voyage'))
                    <p class="help-block">
                        <strong>{{ $errors->first('type_voyage') }}</strong>
                    </p>
                @endif
            </div>
        
        
            <div
                class="col-md-3 {{ $errors->has('ville') ? ' has-error' : '' }}">
                <label for="ville">
                    Ville
                </label>
                <select class="form-control show-tick" name="ville" id="ville"
                        required>
                    @foreach($villes as $ville)
                        <option value="{{$ville->id}}" @if(isset($vol) && $vol->ville_id== $ville->id) selected @endif>
                            {{$ville->nom}}
                        </option>
                    @endforeach
                </select>
                @if ($errors->has('ville'))
                    <p class="help-block">
                        <strong>{{ $errors->first('ville') }}</strong>
                    </p>
                @endif
            </div>
        
        
            <div class="col-md-3 {{ $errors->has('pnr') ? ' has-error' : '' }}">
                <label>PNR</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ old('pnr', $vol->pnr ?? '') }}" name="pnr"
                           required>
                </div>
                @if ($errors->has('pnr'))
                    <p class="help-block">
                        <strong>{{ $errors->first('pnr') }}</strong>
                    </p>
                @endif
            </div>
        <div class="col-md-3 {{ $errors->has('place') ? ' has-error' : '' }}">
            <label>nombre total de places</label>
            <div class="input-group">
                <input id="place" type="number" class="form-control" name="place"
                       value="{{isset($vol) ? $vol->place_total : '1'}}"
                       min="{{(isset($vol) && $operation!='copy') ? $vol->place_reserver : '1'}}" required>
            </div>
            @if ($errors->has('place'))
                <p class="help-block">
                    <strong>{{ $errors->first('place') }}</strong>
                </p>
            @endif
        </div>
        @if(isset($vol) && $operation=='edit')
            <div class="col-md-3 {{ $errors->has('place') ? ' has-error' : '' }}">
                <label>Place reserver</label>
                <div class="input-group">
                    <input id="place" type="number" class="form-control" readonly
                           value="{{$vol->place_reserver}}">
                </div>
                @if ($errors->has('place'))
                    <p class="help-block">
                        <strong>{{ $errors->first('place') }}</strong>
                    </p>
                @endif
            </div>
        @endif
        
            <div class="col-md-3 {{ $errors->has('itineraire') ? ' has-error' : '' }}">
                <label>Itineraire</label>
                <div class="input-group">
                    <input id="itineraire" type="text" class="form-control"
                           name="itineraire" value="{{isset($vol) ? $vol->itineraire : old("itineraire")}}"
                           required>
                </div>
                @if ($errors->has('itineraire'))
                    <p class="help-block">
                        <strong>{{ $errors->first('itineraire') }}</strong>
                    </p>
                @endif
            </div>
            <div class="col-md-3 {{ $errors->has('itineraire_color') ? ' has-error' : '' }}">
                <label>Itineraire coleur</label>
                <div class="input-group">
                    <input id="itineraire_color" type="color" class="form-control"
                           name="itineraire_color"
                           value="{{isset($vol) ? $vol->itineraire_color : old("itineraire_color")}}"
                           required>
                </div>
                @if ($errors->has('itineraire_color'))
                    <p class="help-block">
                        <strong>{{ $errors->first('itineraire_color') }}</strong>
                    </p>
                @endif
            </div>
        
        <div class="col-md-3 {{ $errors->has('condition') ? ' has-error' : '' }}">
            <label>Conditions</label>
            <div class="input-group">
                <select class="form-control" name="condition">
                    @foreach($conditions as $condition)
                        <option value="{{$condition->id}}"
                                @if(isset($vol) && $vol->condition_id== $condition->id) selected @endif>{{$condition->nom}}</option>
                    @endforeach
                </select>
            </div>
            @if ($errors->has('condition'))
                <p class="help-block">
                    <strong>{{ $errors->first('condition') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3 {{ $errors->has('period_voyage') ? ' has-error' : '' }}">
            <label>Période</label>
            <div class="input-group">
                <select class="form-control" name="period_voyage">
                    
                        <option value="Eté" @if(isset($vol) && $vol->period_voyage== "Eté") selected @endif>Eté
                        </option>
                        <option value="Décembre"
                                @if(isset($vol) && $vol->period_voyage== "Décembre") selected @endif>Décembre
                        </option>

                    
                </select>
            </div>
            @if ($errors->has('period_voyage'))
                <p class="help-block">
                    <strong>{{ $errors->first('period_voyage') }}</strong>
                </p>
            @endif
        </div>

        
        <div class="col-md-3 {{ $errors->has('days_before_closure') ? ' has-error' : '' }}">
            <label>Nb jours avant clôture des inscriptions</label>
            <div class="input-group">
                <input type="number" class="form-control" name="days_before_closure" required
                       min="0"
                       value="{{ isset($vol) ? $vol->days_before_closure :old('days_before_closure')}}">
            </div>
            <p class="help-block">
                <b>Note :</b> L'inscription sera clôturée [Jours avant clôture des inscriptions] jours avant la date de
                départ.<br>
                <b>Example:</b> Si "Jours avant clôture des inscriptions" est 2 et que la date de départ du vol est
                15/01/2025, l'inscription sera clôturée à partir du 13/01/2025.
            </p>
            @if ($errors->has('days_before_closure'))
                <p class="help-block">
                    <strong>{{ $errors->first('days_before_closure') }}</strong>
                </p>
            @endif
        </div>
        <div class="col-md-3">
            <label for="is_archive">
                Masquer
            </label>
            <div class="input-group">
                <input type="checkbox" name="is_archive" id="is_archive" value="1"
                    {{ old('archive', isset($vol) ? $vol->is_archive : false) ? 'checked' : '' }}
                       style="width: 30px;height: 30px"/>
            </div>
        </div>

    </div>
</div>
