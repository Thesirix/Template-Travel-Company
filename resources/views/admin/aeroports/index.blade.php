@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
@include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Aéroports
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Aéroports</li>
            </ol>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header ">
                        <h6 class="m-0 font-weight-bold text-primary"> Ajouter Aéroport
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="demo-form2" data-parsley-validate class="row "
                              method="post" action="{{route('admin.aeroports.store')}}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="col-3">
                                <label for="name">Nom
                                    <span class="required">*</span>
                                </label>
                                <div class=" {{ $errors->has('name') ? ' has-error' : '' }}">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" required autofocus placeholder="AirPort">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="abv">abréviation
                                    <span class="required">*</span>
                                </label>
                                <div class=" {{ $errors->has('abv') ? ' has-error' : '' }}">
                                    <input id="abv" type="text" class="form-control" name="abv"
                                           value="{{ old('abv') }}" required autofocus placeholder="abréviation">

                                    @if ($errors->has('abv'))
                                        <span class="help-block">
                                                    <strong>{{ $errors->first('abv') }}</strong>
                                                </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="ville">Ville
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control show-tick" name="ville" id="ville">

                                    @foreach($villes as $ville)

                                        <option value="{{$ville->id}}">{{$ville->nom}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body table-responsive">
                        <table class="table table-flush">
                            <thead class="thead-light">
                            <tr>
                                <th>Nom</th>
                                <th>ville</th>
                                <th>pays</th>
                                <th>abréviation</th>
                                <th class="column-title no-link last"><span class="nobr">Action</span>
                                </th>

</tr>
                            </thead>

                            <tbody>

                            @foreach($airPorts as $air)
                                <tr class="even pointer">

                                    <td class=" ">{{$air->nom}}</td>
                                    <td class=" ">{{$air->ville->nom}}</td>
                                    <td class=" ">{{$air->ville->country->nom}}</td>
                                    <td class=" ">{{$air->abv}}</td>
                                    <td class="d-flex ">

                                        <a href="{{ route('admin.aeroports.edit',$air) }}">
                                            <button type="button" class="btn btn-primary waves-effect">Modifier
                                            </button>
                                        </a>

<form method="post" action="{{ route('admin.aeroports.destroy',$air) }}"
                                              style="display: inline-table;"
                                              onsubmit="return confirm('Vous êtes sûre?')">
                                            {{ csrf_field() }}
                                            {{ method_field('delete') }}

                                            <button type="submit" class="btn btn-danger waves-effect">Supprimer
                                            </button>
                                        </form>

</td>

                                </tr>
                            @endforeach

</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
