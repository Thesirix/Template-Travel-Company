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
                Modifier Pays
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
                    <h6 class="m-0 font-weight-bold text-primary">Ajouter</h6>
                </div>
                <form id="demo-form2" data-parsley-validate class="card-body row"
                      method="post" action="{{route('admin.pays.update',$country)}}"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}

                    {{ csrf_field() }}
                    <div class="col-xl-4 mt-4">
                        <label for="name">Nom
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                            <input id="name" type="text" class="form-control" name="name"
                                   value="{{ $country->nom }}" required autofocus placeholder="Country">
                            @if ($errors->has('name'))
                                <p class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="show">Afficher
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('show') ? ' has-error' : '' }}">
                            <select class="form-control" name="show" id="show">
                                <option value="1" @if($country->show==true) selected @endif>OUI</option>
                                <option value="0" @if($country->show==false) selected @endif>NON</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="chart">Histogramme
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('chart') ? ' has-error' : '' }}">
                            <select class="form-control" name="chart" id="chart">
                                <option value="1" @if($country->chart==true) selected @endif>OUI</option>
                                <option value="0" @if($country->chart==false) selected @endif>NON</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="upload_image">Image
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image" src="{{ $country->image }}"
                                             style="width:100px;"/><br/>
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
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
