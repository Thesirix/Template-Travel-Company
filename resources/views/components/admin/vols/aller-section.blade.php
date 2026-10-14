@props(['destination', 'companies', 'airPorts', 'vol' => null])
<div class="aller_vol aller-section">
    <div class="card my-5">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-primary" id="h1_aller">
                <i class="fas fa-plane-departure"></i>
                <span class="aller_name">Aller</span>
            </h4>
        </div>
        <div class="card-body ">
            <div class="row clearfix">
                <div
                    class="col-md-3 premier_add {{ $errors->has('company') ? ' has-error' : '' }}">
                    <label>
                        Companies
                    </label>
                    <select class="form-control show-tick" name="company" id="company_select"
                             required>
                        <option></option>
                        @foreach($companies as $company)
                            @if($company->show)
                                <option value="{{$company->id}}" ty="{{$company->type}}"
                                        id="sel_opt_{{$company->id}}"
                                >{{$company->nom}} - {{$company->country->nom}}</option>
                            @endif
                        @endforeach
                    </select>
                    @if ($errors->has('company'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('company') }}</strong>
                                                </span>
                    @endif

                </div>
                <div class="col-md-4 premier_add" style="display: none">
                    <label>
                        Vol
                    </label>
                    <select class="form-control show-tick" name="type" id="type_select_AR">
                        <option value="0">Aller</option>
                        <option value="1">Retour</option>
                    </select>

                </div>
                <div
                    class="col-md-3 premier_add {{ $errors->has('air_de_a_0') ? ' has-error' : '' }}">
                    <label>
                        aéroport de depart
                    </label>
                    <select class="form-control show-tick" name="air_de_a_0"
                            id="airPort_escale">

                        @foreach($airPorts as $air)

                            <option value="{{$air->id}}">{{$air->abv}}
                                - {{$air->ville->nom}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('air_de_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_de_a_0') }}</strong>
                                                </span>
                    @endif

                </div>
                <div class="col-md-3 premier_add {{ $errors->has('air_a_a_0') ? ' has-error' : '' }}">
                    <label>
                        aéroport d'arriver
                    </label>
                    <select class="form-control show-tick" name="air_a_a_0" id="air_a_a_0">
                        @foreach($airPorts as $air)

                            <option value="{{$air->id}}">{{$air->abv}}
                                - {{$air->ville->nom}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('air_a_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_a_a_0') }}</strong>
                                                </span>
                    @endif

                </div>
            </div>
            <div class="row ">
                <div class="col-md-3 {{ $errors->has('date_de_a_0') ? ' has-error' : '' }}"
                     id="Date_depart">
                    <label>Date de depart</label>
                    <div class="input-group">
                        <input type="date" class="form-control date" name="date_de_a_0"
                               id="date_de_a_0" placeholder="Ex: 30/07/2016"
                               value="{{ old('date_de_a_0') }}" required>
                    </div>
                    @if ($errors->has('date_de_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('date_de_a_0') }}</strong>
                                                </span>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('heure_de_a_0') ? ' has-error' : '' }}"
                     id="heure_depart">
                    <label>heure de depart</label>
                    <div class="input-group">
                        <input type="time" class="form-control time24 temp"
                               placeholder="Ex: 23:59" name="heure_de_a_0"
                               value="{{ old('heure_de_a_0') }}" required hd="heure_de_a_0"
                               ha="heure_a_a_0" temp="temp_a_0"
                               id="heure_de_a_0" hv="" dv="">

                    </div>
                    @if ($errors->has('heure_de_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_de_a_0') }}</strong>
                                                </span>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('heure_a_a_0') ? ' has-error' : '' }}">
                    <label style="color:red">heure d'arriver</label>
                    <div class="input-group">

                        <input type="time" class="form-control time24 temp"
                               placeholder="Ex: 23:59" name="heure_a_a_0"
                               value="{{ old('heure_a_a_0') }}" required hd="heure_de_a_0"
                               ha="heure_a_a_0" temp="temp_a_0"
                               id="heure_a_a_0">

                    </div>
                    @if ($errors->has('heure_a_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_a_a_0') }}</strong>
                                                </span>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('temp_a_0') ? ' has-error' : '' }}">
                    <label style="color:red">le temp de vol</label>
                    <div class="input-group">
                        <input type="text" class="form-control " placeholder="Ex: 23:59"
                               name="temp_a_0" value="{{ old('temp_a_0') }}" required
                               id="temp_a_0">

                    </div>
                    @if ($errors->has('temp_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('temp_a_0') }}</strong>
                                                </span>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('modele_a_0') ? ' has-error' : '' }}">
                    <label style="">Modele</label>
                    <div class="input-group">
                        <input type="text" class="form-control "
                               name="modele_a_0" value="{{ old('modele_a_0') }}" id="modele_a_0"
                        >
                    </div>
                    @if ($errors->has('modele_a_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('modele_a_0') }}</strong>
                                                </span>
                    @endif
                </div>
                
            </div>

        </div>
    </div>
    <div id="content_escale_a"></div>
    <div class="row">
        <button type="button" class="btn btn-info btn-sm" id="escale_btn_a"
                style="margin:auto;">Ajouter un Escale
        </button>
    </div>
</div>
