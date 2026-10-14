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
                Modifier Aéroport
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Aéroports Aéroport</li>
            </ol>
        </div>
        <div class="">
            <div class="row">
                <div class="col-md-12 col-sm-12 ">
                    <div class=card>
                        <div class="card-body">
                            <form id="demo-form2" data-parsley-validate class="row"
                                  method="post" action="{{route('admin.aeroports.update',$air)}}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}
                                <div class="col-3">
                                    <label for="first-name">Nom
                                        <span class="required">*</span>
                                    </label>
                                    <div class=" {{ $errors->has('name') ? ' has-error' : '' }}">
                                        <input id="name" type="text" class="form-control" name="name"
                                               value="{{ $air->nom }}" required autofocus placeholder="AirPort">

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label for="first-name">abréviation
                                        <span class="required">*</span>
                                    </label>
                                    <div class=" {{ $errors->has('abv') ? ' has-error' : '' }}">
                                        <input id="abv" type="text" class="form-control" name="abv"
                                               value="{{ $air->abv }}" required autofocus placeholder="abréviation">

                                        @if ($errors->has('abv'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('abv') }}</strong>
                                                </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-3">
                                    <label for="first-name">Pays
                                        <span class="required">*</span>
                                    </label>
                                    <div>
                                        <select class="form-control show-tick" name="ville">

                                            <option value="{{$air->ville->id}}">{{$air->ville->nom}}</option>
                                            @foreach($villes as $ville)

                                                <option value="{{$ville->id}}">{{$ville->nom}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
