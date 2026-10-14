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
                Companies
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Companies</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header ">
                        <h6 class="m-0 font-weight-bold text-primary"> Ajouter Companie</h6>
                    </div>
                    <div class="card-body">
                        <form id="demo-form2" class="row" method="post" action="{{route('admin.compagnies.store')}}"
                              enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="col-3">
                                <label for="name">Nom <span
                                        class="required">*</span>
                                </label>
                                <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" required autofocus placeholder="Company">
                                    @if ($errors->has('name'))
                                        <p class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="country">Pays <span
                                        class="required">*</span>
                                </label>
                                <select class="form-control show-tick" name="country" id="country">
                                    @foreach($countries as $contry)
                                        <option value="{{$contry->id}}">{{$contry->nom}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="type">Type <span
                                        class="required">*</span>
                                </label>
                                <select class="form-control show-tick" name="type" id="type">
                                    <option value="1">aller retour</option>
                                    <option value="0">aller simple</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="show">Afficher
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control show-tick" name="show" id="show">
                                    <option value="1">OUI</option>
                                    <option value="0">NON</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="chart">Histogramme
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control show-tick" name="chart" id="chart">
                                    <option value="1">OUI</option>
                                    <option value="0">NON</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="id_image">Image <span class="required">*</span></label>
                                <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                                    <table>
                                        <tr>
                                            <td>
                                                <img id="id_image" src="" style="width:100px;display:none"/><br/>
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
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Companies</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-flush">
                                <thead>
                                <tr class="headings">

                                    <th class="column-title">Nom</th>
                                    <th class="column-title">type</th>
                                    <th class="column-title">afficher</th>
                                    <th class="column-title">Histogramme</th>
                                    <th class="column-title">image</th>
                                    <th class="column-title">pays</th>

                                    <th class="column-title no-link last"><span class="nobr">Action</span>
                                    </th>
                                    <th class="bulk-actions" colspan="7">
                                        <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span
                                                class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                                    </th>
                                </tr>
                                </thead>

                                <tbody>

                                @foreach($companies as $company)
                                    <tr class="even pointer">

                                        <td class=" ">{{$company->nom}}</td>
                                        <td class=" ">

                                            @if($company->type)
                                                aller  retour
                                            @else
                                                aller simple
                                            @endif
                                        </td>
                                        <td class=" ">

                                            @if($company->show)
                                                OUI
                                            @else
                                                NON
                                            @endif
                                        </td>
                                        <td class=" ">

                                            @if($company->chart)
                                                OUI
                                            @else
                                                NON
                                            @endif
                                        </td>
                                        <td class=" ">
                                            <img src="{{ $company->image }}"
                                                 style="width: auto;height: 20px;">
                                        </td>
                                        <td class=" ">{{$company->country->nom}}</td>
                                        <td class="d-flex flex-row ">
                                            <a href="{{ route('admin.compagnies.edit',$company) }}"
                                               class="btn btn-primary waves-effect">
                                                Modifier
                                            </a>
                                            <form method="post" action="{{ route('admin.compagnies.destroy',$company) }}"
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
    </div>



@endsection
