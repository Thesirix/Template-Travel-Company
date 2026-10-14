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
                Modifier Ville
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Modifier Ville</li>
            </ol>
        </div>

        <div class="row">
            <div class="card col-12">

                <form id="demo-form2" data-parsley-validate class="card-body d-flex flex-wrap"
                      method="post" action="{{route('admin.villes.update',$ville)}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="mr-3">
                        <label  for="name">
                            Nom
                        </label>
                        <input id="name" type="text" class="form-control" name="name" style="width: 200px"
                               value="{{ $ville->nom }}" required autofocus placeholder="Country">

                        @if ($errors->has('name'))
                            <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                        @endif
                    </div>

                    <div class="mr-3">
                        <label  for="country">
                            Pays
                        </label>
                        <select class="form-control show-tick" name="country" id="country" style="width: 200px">

                            @foreach($countries as $contry)

                                <option value="{{$contry->id}}">{{$contry->nom}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mr-3">
                        <label  for="coleur">
                            Coleur
                        </label>
                        <input id="coleur" type="color" class="form-control" name="coleur"
                               value="{{ $ville->coleur }}" required autofocus >

                        @if ($errors->has('coleur'))
                            <span class="help-block">
                                                    <strong>{{ $errors->first('coleur') }}</strong>
                                                </span>
                        @endif
                    </div>

                    <div class="d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection
