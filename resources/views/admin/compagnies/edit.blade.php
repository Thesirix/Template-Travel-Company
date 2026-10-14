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
                Modifier Companie
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Conditions</li>
                <li class="breadcrumb-item">Modifier Conditions</li>

            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Modifier</h6>
                </div>
                <form id="demo-form2" data-parsley-validate class="row card-body" method="post"
                      action="{{route('admin.compagnies.update',$company)}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    {{ csrf_field() }}
                    <div class="col-xl-4 mt-4">
                        <label for="first-name">Nom <span
                                class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                            <input id="name" type="text" class="form-control" name="name" value="{{ $company->nom }}"
                                   required autofocus placeholder="Company">
                            @if ($errors->has('name'))
                                <p class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="first-name">Pays <span
                                class="required">*</span>
                        </label>
                        <select class="form-control show-tick" name="country">
                            <option value="{{$company->country->id}}">{{$company->country->nom}}</option>
                            @foreach($countries as $contry)
                                <option value="{{$contry->id}}">{{$contry->nom}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="first-name">Type <span
                                class="required">*</span>
                        </label>
                        <select class="form-control show-tick" name="type">
                            <option value="{{$company->type}}">
                                @if($company->type==true)
                                    aller  retour
                                @else
                                    aller simple
                                @endif
                            </option>
                            <option value="1">aller retour</option>
                            <option value="0">aller simple</option>
                        </select>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="first-name">Afficher <span
                                class="required">*</span>
                        </label>
                        <select class="form-control show-tick" name="show">
                            <option value="{{$company->type}}">
                                @if($company->show==true)
                                    OUI
                                @else
                                    NON
                                @endif
                            </option>
                            <option value="1">OUI</option>
                            <option value="0">NON</option>
                        </select>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="first-chart">Histogramme <span
                                class="required">*</span>
                        </label>
                        <select class="form-control show-tick" name="chart">
                            <option value="{{$company->type}}">
                                @if($company->chart==true)
                                    OUI
                                @else
                                    NON
                                @endif
                            </option>
                            <option value="1">OUI</option>
                            <option value="0">NON</option>
                        </select>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="last-name">Image <span
                                class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image" src="{{ $company->image }}" style="width:100px;"/><br/>
                                        <input id="upload_image" type="file" name="image"
                                               onchange="PreviewImage('upload_image','id_image');"/>
                                    </td>
                                </tr>
                            </table>
                            @if ($errors->has('image'))
                                <p class="help-block">
                                    <strong>{{ $errors->first('image') }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-12 mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
