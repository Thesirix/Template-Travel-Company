@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
@include('admin.partials.top-nav')
@endsection
@section('content')

    <div class="container-fluid py-4" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Copier vol
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion vols</li>
                <li class="breadcrumb-item">Copier vol</li>
            </ol>
        </div>

        <form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left"
              method="post" action="{{route('admin.vols.copy.store', $vol->destination)}}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="nb_escale_a" id="nb_escale_a" value="1">
            <input type="hidden" name="nb_escale_r" id="nb_escale_r" value="1">
            <input type="hidden" name="type_r_0" value="1">
            <input type="hidden" name="type_a_0" value="0">
            <input type="hidden" name="destination" value="{{$vol->destination}}">

            <input type="hidden" name="oldVolId" value="{{$vol->id}}">

            <x-admin.vols.information-general-section :villes="$villes" :conditions="$conditions"
                                                        :destination="$destination" :groups="$groups"
                                                        :vol="$vol" operation="copy"/>

<!-----------------------------  aller---------------------------------------->
            <div>
                @foreach($vol->escaliers->where('type',0) as $escalier)
                    <div class="card my-5">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h4 class="m-0 font-weight-bold text-primary " id="h1_aller">
                                <i class="fas fa-plane-departure"></i>
                                <span class="aller_name">Aller</span>
                            </h4>
                        </div>
                        <div class="card-body ">
                            <div class="row clearfix">
                                @if($loop->first)
                                    <div
                                        class="col-md-4 premier_add {{ $errors->has('company') ? ' has-error' : '' }}">
                                        <p>
                                            Companies
                                        </p>
                                        <select class="form-control show-tick" name="company" id="company_select" required>
                                            <option></option>
                                            @foreach($companies as $company)
                                                @if($company->show)
                                                    <option value="{{$company->id}}" ty="{{$company->type}}"
                                                            id="sel_opt_{{$company->id}}"
                                                            @if($company->id==$vol->company->id) selected @endif
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
                                @endif
                                <div class="col-md-4 premier_add" id="type_div_AR" style="display: none">
                                    <p>
                                        Vol
                                    </p>
                                    <select class="form-control show-tick" name="type" id="type_select_AR">
                                        <option value="0">Aller</option>
                                        <option value="1">Retour</option>
                                    </select>
                                </div>
                                <div
                                    class="col-md-4 premier_add {{ $errors->has('air_de_a_'.$loop->index) ? ' has-error' : '' }}">
                                    <p>
                                        aéroport de depart
                                    </p>
                                    <select class="form-control show-tick" name="air_de_a_{{$loop->index}}"
                                            id="airPort_escale">
                                        @foreach($AirPorts as $air)
                                            <option value="{{$air->id}}"
                                                    @if($escalier->air_port_id==$air->id) selected @endif>{{$air->abv}}
                                                - {{$air->ville->nom}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('air_de_a_0'))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_de_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif

                                </div>
                                <div
                                    class="col-md-4 premier_add {{ $errors->has('air_a_a_'.$loop->index) ? ' has-error' : '' }}">
                                    <p>
                                        aéroport d'arriver
                                    </p>
                                    <select class="form-control show-tick" name="air_a_a_{{$loop->index}}">

                                        @foreach($AirPorts as $air)
                                            <option value="{{$air->id}}"
                                                    @if($escalier->re_air_port_id==$air->id) selected @endif>{{$air->abv}}
                                                - {{$air->ville->nom}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('air_a_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_a_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif

                                </div>

                            </div>
                            <div class="row ">
                                <div class="col-md-3 {{ $errors->has('date_de_a_'.$loop->index) ? ' has-error' : '' }}"
                                     id="Date_depart">
                                    <p>Date de depart</p>
                                    <div class="input-group">

                                        <input type="date" class="form-control date" name="date_de_a_{{$loop->index}}"
                                               id="date_de_a_0{{$loop->index}}" placeholder="Ex: 30/07/2016"
                                               value="{{$escalier->date_depart}}" required>

                                    </div>
                                    @if ($errors->has('date_de_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('date_de_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('heure_de_a_'.$loop->index) ? ' has-error' : '' }}"
                                     id="heure_depart">
                                    <p>heure de depart</p>
                                    <div class="input-group">
                                        <input type="time" class="form-control time24 temp"
                                               placeholder="Ex: 23:59" name="heure_de_a_{{$loop->index}}"
                                               value="{{$escalier->heure_depart}}" required
                                               hd="heure_de_a_{{$loop->index}}"
                                               ha="heure_a_a_{{$loop->index}}" temp="temp_a_{{$loop->index}}"
                                               id="heure_de_a_{{$loop->index}}" hv="" dv="">

                                    </div>
                                    @if ($errors->has('heure_de_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_de_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('heure_a_a_'.$loop->index) ? ' has-error' : '' }}">
                                    <p style="color:red">heure d'arriver</p>
                                    <div class="input-group">

                                        <input type="time" class="form-control time24 temp"
                                               placeholder="Ex: 23:59" name="heure_a_a_{{$loop->index}}"
                                               value="{{$escalier->heure_arriver}}" required
                                               hd="heure_de_a_{{$loop->index}}"
                                               ha="heure_a_a_{{$loop->index}}" temp="temp_a_{{$loop->index}}"
                                               id="heure_a_a_{{$loop->index}}">

                                    </div>
                                    @if ($errors->has('heure_a_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_a_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('temp_a_'.$loop->index) ? ' has-error' : '' }}">
                                    <p style="color:red">le temp de vol</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control " placeholder="Ex: 23:59"
                                               name="temp_a_{{$loop->index}}" value="{{$escalier->temp}}" required
                                               id="temp_a_{{$loop->index}}">
                                    </div>
                                    @if ($errors->has('temp_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('temp_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('modele_a_'.$loop->index) ? ' has-error' : '' }}">
                                    <p style="">Modele</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                               name="modele_a_{{$loop->index}}" value="{{$escalier->model}}"
                                               id="modele_a_0"
                                        >
                                    </div>
                                    @if ($errors->has('modele_a_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('modele_a_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                
                            </div>
                        </div>
                    </div>
                @endforeach
                <div id="content_escale_a"></div>
            </div>
            <!-----------------------------  retour---------------------------------------->

            <div class="retour_vol retour-section">
                @foreach($vol->escaliers->where('type',1) as $escalier)
                    <div class="card my-5">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h4 class="m-0 font-weight-bold text-primary" id="h1_aller">
                                Retour
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row clearfix">
                                <div
                                    class="col-md-6  {{ $errors->has('air_de_r_'.$loop->index)}}) ? ' has-error' : '' }}">
                                    <p>
                                        aéroport de depart
                                    </p>
                                    <select class="form-control show-tick" name="air_de_r_{{$loop->index}}">
                                        @foreach($AirPorts as $air)

                                            <option value="{{$air->id}}"
                                                    @if($escalier->air_port_id==$air->id) selected @endif>{{$air->abv}}
                                                - {{$air->ville->nom}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('air_de_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_de_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif

                                </div>
                                <div class="col-md-6 {{ $errors->has('air_a_r_'.$loop->index) ? ' has-error' : '' }}">
                                    <p>
                                        aéroport d'arriver
                                    </p>
                                    <select class="form-control show-tick" name="air_a_r_{{$loop->index}}"
                                            id="air_a_r_{{$loop->index}}">
                                        @foreach($AirPorts as $air)

                                            <option value="{{$air->id}}"
                                                    @if($escalier->re_air_port_id==$air->id) selected @endif>{{$air->abv}}
                                                - {{$air->ville->nom}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('air_a_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('air_a_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif

                                </div>

                            </div>
                            <div class="row ">
                                <div class="col-md-3 {{ $errors->has('date_de_r_'.$loop->index) ? ' has-error' : '' }}">
                                    <p>Date de depart</p>
                                    <div class="input-group">

                                        <input type="date" class="form-control date" name="date_de_r_{{$loop->index}}"
                                               placeholder="Ex: 30/07/2016" value="{{$escalier->date_depart}}"
                                               id="date_de_r_{{$loop->index}}">

                                    </div>
                                    @if ($errors->has('date_de_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('date_de_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div
                                    class="col-md-3 {{ $errors->has('heure_de_r_'.$loop->index) ? ' has-error' : '' }}">
                                    <p>heure de depart</p>
                                    <div class="input-group">
                                        <input type="time" class="form-control time24 temp"
                                               placeholder="Ex: 23:59" name="heure_de_r_{{$loop->index}}"
                                               value="{{$escalier->heure_depart}}" hd="heure_de_r_{{$loop->index}}"
                                               ha="heure_a_r_{{$loop->index}}" temp="temp_r_{{$loop->index}}"
                                               id="heure_de_r_{{$loop->index}}" hv="">

                                    </div>
                                    @if ($errors->has('heure_de_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_de_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('heure_a_r_0') ? ' has-error' : '' }}">
                                    <p style="color:red">heure d'arriver</p>
                                    <div class="input-group">
                                        <input type="time" class="form-control time24 temp"
                                               placeholder="Ex: 23:59" name="heure_a_r_{{$loop->index}}"
                                               value="{{$escalier->heure_arriver}}" hd="heure_de_r_{{$loop->index}}"
                                               ha="heure_a_r_{{$loop->index}}" temp="temp_r_{{$loop->index}}"
                                               id="heure_a_r_{{$loop->index}}" hv="">

                                    </div>
                                    @if ($errors->has('heure_a_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('heure_a_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('temp_r_'.$loop->index) ? ' has-error' : '' }}">
                                    <p style="color:red">le temp de vol</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control " placeholder="Ex: 23:59"
                                               name="temp_r_{{$loop->index}}" value="{{$escalier->temp}}"
                                               id="temp_r_{{$loop->index}}"
                                        >
                                    </div>
                                    @if ($errors->has('temp_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('temp_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>
                                <div class="col-md-3 {{ $errors->has('modele_r_'.$loop->index) ? ' has-error' : '' }}">
                                    <p style="">Modele</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control "
                                               name="modele_r_{{$loop->index}}" value="{{$escalier->model}}"
                                               id="modele_r_0">
                                    </div>
                                    @if ($errors->has('modele_r_'.$loop->index))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('modele_r_'.$loop->index) }}</strong>
                                                </span>
                                    @endif
                                </div>

</div>
                        </div>
                    </div>
                @endforeach

                <div class="row">

</div>

<div id="content_escale_r">
                </div>

{{--                            style="margin: auto">Ajouter un Escale--}}

</div>

<x-admin.vols.schedule-section :vol="$vol"/>

<x-admin.vols.passager-prices-section :destination="$destination" :vol="$vol"/>
            
            <x-admin.vols.packages-section :vol="$vol"/>

            <x-admin.vols.formalite-section :destination="$destination" :vol="$vol"/>

<x-admin.vols.canceling-condition-section :vol="$vol"/>

<div class="col-md-3">
                <button type="submit" class="btn btn-primary row" style="margin: auto;">
                    Enregistrer le Vol
                </button>
            </div>

</form>

    </div>

@endsection
@push("script")
    <script src="https://cdn.ckeditor.com/ckeditor5/32.0.0/classic/ckeditor.js"></script>
    <script>
        var editors = document.querySelectorAll('.editor')
        for (let i = 0; i < editors.length; i++) {
            ClassicEditor
                .create(editors[i])
                .catch(error => {
                    console.error(error);
                });
        }
    </script>

    {{--    aller simple--}}
    <script>
        function type_voyage() {

            if ($('#type_voyage').val() == 'aller_simple') {
                $('.retour-section').hide();
                $('.aller_name').text('Aller')
            } else if ($('#type_voyage').val() == 'retour_simple') {
                $('.retour-section').hide();
                $('.aller_name').text('Retour')
            } else {
                $('.retour-section').show();
                $('.aller_name').text('Aller')
            }

        }

        $(window).ready(function () {
            type_voyage()
        });
        $('#type_voyage').change(function (e) {
            type_voyage()
        })
    </script>

@endpush
