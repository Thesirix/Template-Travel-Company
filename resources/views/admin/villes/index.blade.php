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
                Villes
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion Villes</li>
            </ol>
        </div>
        <div class="col-12">
            <div class="card mb-4 ">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Ajouter une ville</h6>
                </div>
                <form class="card-body d-flex flex-wrap" id="demo-form2" data-parsley-validate
                      method="post" action="{{route('admin.villes.store')}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class=" {{ $errors->has('name') ? ' has-error' : '' }} mr-3">
                        <label for="first-name">
                            Nom
                        </label>
                        <input id="name" type="text" class="form-control" name="name" style="width: 200px"
                               value="{{ old('name') }}" required autofocus placeholder="ville">
                        @if ($errors->has('name'))
                            <p class="help-block">
                                <strong>{{ $errors->first('name') }}</strong>
                            </p>
                        @endif
                    </div>
                    <div class="mr-3">
                        <label for="first-name">
                            Pays
                        </label>
                        <select class="form-control show-tick" name="country" style="width: 200px">
                            @foreach($countries as $contry)
                                <option value="{{$contry->id}}">{{$contry->nom}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mr-3">
                        <label for="first-name">
                            Coleur
                        </label>
                        <input type="color" name="coleur" class="form-control">
                    </div>

                    <div class=" d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
            <div class="card mb-4">
                <div class="table-responsive">
                    <table class="table table-flush">
                        <thead>
                        <tr class="headings">

                            <th class="column-title">Nom</th>
                            <th class="column-title">Pays</th>
                            <th class="column-title">Coleur</th>
                            <th class="column-title no-link last"><span class="nobr">Action</span>
                            </th>
                            <th class="bulk-actions" colspan="7">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span
                                        class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                            </th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($villes as $ville)
                            <tr class="even pointer">

                                <td class=" ">{{$ville->nom}}</td>
                                <td class=" ">{{$ville->country->nom}}</td>
                                <td class=" " > <div style="background-color: {{$ville->coleur}};width: 50px;height: 20px"></div></td>
                                <td class=" ">
                                    <a href="{{ route('admin.villes.edit',$ville) }}">
                                        <button type="button" class="btn btn-primary waves-effect">Modifier
                                        </button>
                                    </a>

                                    <form method="post" action="{{ route('admin.villes.destroy',$ville) }}"
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



@endsection
