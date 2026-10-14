@props(['destination', 'companies', 'airPorts', 'vol' => null])
<div>
    <div class="card  my-5 retour-section">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-plane-arrival"></i>
                Retour
            </h4>
        </div>
        <div class="card-body ">
            <div class="row clearfix">
                <div class="col-md-6  {{ $errors->has('air_de_r_0') ? ' has-error' : '' }}">
                    <label>aéroport de depart</label>
                    <select class="form-control show-tick" name="air_de_r_0">
                        <option></option>
                        @foreach($airPorts as $air)

                            <option value="{{$air->id}}">{{$air->abv}}
                                - {{$air->ville->nom}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('air_de_r_0'))
                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_de_r_0') }}</strong>
                                                </span>
                    @endif

                </div>
                <div class="col-md-6 {{ $errors->has('air_a_r_0') ? ' has-error' : '' }}">
                    <label>aéroport d'arriver</label>
                    <select class="form-control show-tick" name="air_a_r_0" id="air_a_r_0">
                        <option></option>
                        @foreach($airPorts as $air)
                            <option value="{{$air->id}}">{{$air->abv}}
                                - {{$air->ville->nom}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('air_a_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('air_a_r_0') }}</strong>
                        </p>
                    @endif
                </div>
            </div>
            <div class="row ">
                <div class="col-md-3 {{ $errors->has('date_de_r_0') ? ' has-error' : '' }}">
                    <label>Date de depart</label>
                    <div class="input-group">
                        <input type="date" class="form-control date" name="date_de_r_0"
                               placeholder="Ex: 30/07/2016" value="{{ old('date_de_r_0') }}"
                               id="date_de_r_0">
                    </div>
                    @if ($errors->has('date_de_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('date_de_r_0') }}</strong>
                        </p>
                    @endif
                </div>
                <div
                    class="col-md-3 {{ $errors->has('heure_de_r_0') ? ' has-error' : '' }}">
                    <label>heure de depart</label>
                    <div class="input-group">
                        <input type="time" class="form-control time24 temp"
                               placeholder="Ex: 23:59" name="heure_de_r_0"
                               value="{{ old('heure_de_r_0') }}" hd="heure_de_r_0"
                               ha="heure_a_r_0" temp="temp_r_0"
                               id="heure_de_r_0" hv="">
                    </div>
                    @if ($errors->has('heure_de_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('heure_de_r_0') }}</strong>
                        </p>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('heure_a_r_0') ? ' has-error' : '' }}">
                    <label style="color:red">heure d'arriver</label>
                    <div class="input-group">
                        <input type="time" class="form-control time24 temp"
                               placeholder="Ex: 23:59" name="heure_a_r_0"
                               value="{{ old('heure_a_r_0') }}" hd="heure_de_r_0"
                               ha="heure_a_r_0" temp="temp_r_0"
                               id="heure_a_r_0" hv="">
                    </div>
                    @if ($errors->has('heure_a_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('heure_a_r_0') }}</strong>
                        </p>
                    @endif
                </div>
                <div class="col-md-3 {{ $errors->has('temp_r_0') ? ' has-error' : '' }}">
                    <label style="color:red">le temp de vol</label>
                    <div class="input-group">
                        <input type="text" class="form-control " placeholder="Ex: 23:59"
                               name="temp_r_0" value="{{ old('temp_r_0') }}" id="temp_r_0">
                    </div>
                    @if ($errors->has('temp_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('temp_r_0') }}</strong>
                        </p>
                    @endif
                </div>

                <div class="col-md-3 {{ $errors->has('modele_r_0') ? ' has-error' : '' }}">
                    <label style="">Modele</label>
                    <div class="input-group">
                        <input type="text" class="form-control "
                               name="modele_r_0" value="{{ old('modele_r_0') }}" id="modele_r_0">
                    </div>
                    @if ($errors->has('modele_r_0'))
                        <p class="help-block">
                            <strong>{{ $errors->first('modele_r_0') }}</strong>
                        </p>
                    @endif
                </div>
                

            </div>
        </div>
    </div>
    <div id="content_escale_r" class="retour-section"></div>
    <div class="row retour-section">
        <button type="button" class="btn btn-info btn-sm" id="escale_btn_r"
                style="margin: auto">Ajouter un Escale
        </button>
    </div>
</div>
