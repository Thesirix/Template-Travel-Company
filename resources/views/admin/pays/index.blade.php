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
                Pays
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Conditions</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header ">
                        <h6 class="m-0 font-weight-bold text-primary"> Ajouter Pays
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="demo-form2" data-parsley-validate class="row"
                              method="post" action="{{route('admin.pays.store')}}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="col-3">
                                <label for="name">Nom
                                    <span class="required">*</span>
                                </label>
                                <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" required autofocus placeholder="Country">

                                    @if ($errors->has('name'))
                                        <p class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-3">
                                <label for="show">Afficher
                                    <span class="required">*</span>
                                </label>
                                <div class="{{ $errors->has('show') ? ' has-error' : '' }}">
                                    <select class="form-control" name="show" id="show">
                                        <option value="1">OUI</option>
                                        <option value="0">NON</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-3">
                                <label for="chart">Histogramme
                                    <span class="required">*</span>
                                </label>
                                <div class="{{ $errors->has('chart') ? ' has-error' : '' }}">
                                    <select class="form-control" name="chart" id="chart">
                                        <option value="1">OUI</option>
                                        <option value="0">NON</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="image">Image
                                    <span class="required">*</span>
                                </label>
                                <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                                    <input id="upload_image" type="file" name="image" id="image"
                                           onchange="PreviewImage('upload_image','id_image');"/>
                                    @if ($errors->has('image'))
                                        <p class="help-block">
                                            <strong>{{ $errors->first('image') }}</strong>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-3 d-flex align-items-end mt-3">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Pays</h6>
                    </div>
                    <div class="table-responsive">

                        <table class="table table-flush">
                            <thead>
                            <tr class="headings">

                                <th class="column-title">Nom</th>
                                <th class="column-title">Image</th>
                                <th class="column-title">Afficher</th>
                                <th class="column-title">Histogramme</th>
                                <th class="column-title no-link last"><span class="nobr">Action</span>
                                </th>
                                <th class="bulk-actions" colspan="7">
                                    <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span
                                            class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                                </th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach($pays as $country)
                                <tr class="even pointer">

                                    <td class=" ">{{$country->nom}}</td>
                                    <td class=" "><img src="{{ $country->image }}"
                                                       style="width: auto;height: 20px;">
                                    </td>
                                    <td>
                                        @if($country->show==false)
                                            NON
                                        @else
                                            OUI
                                        @endif
                                    </td>
                                    <td>
                                        @if($country->chart==false)
                                            NON
                                        @else
                                            OUI
                                        @endif
                                    </td>
                                    <td class=" ">

                                        <a href="{{ route('admin.pays.edit',$country) }}">
                                            <button type="button" class="btn btn-primary waves-effect">Modifier
                                            </button>
                                        </a>


                                        <form method="post" action="{{ route('admin.pays.destroy',$country) }}"
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
@section('script')


@endsection
