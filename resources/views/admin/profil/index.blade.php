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
                Profile
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Profile</li>
            </ol>
        </div>
        <div class="row">
            <div class="card mb-4 col-md-12 col-sm-12">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Profile </h6>
                </div>
                <form id="demo-form2" data-parsley-validate class="card-body"
                      method="post" action="{{route('admin.profil.update',$user)}}"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="item form-group">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-prenom">Prénom
                            <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 {{ $errors->has('prenom') ? ' has-error' : '' }}">
                            <input id="prenom" type="text" class="form-control" name="prenom"
                                   value="{{ $user->prenom }}" required autofocus>

                            @if ($errors->has('prenom'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('prenom') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="item form-group">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="first-name">Nom
                            <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 {{ $errors->has('name') ? ' has-error' : '' }}">
                            <input id="name" type="text" class="form-control" name="name"
                                   value="{{ $user->name }}" required autofocus>

                            @if ($errors->has('name'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="item form-group">


                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="last-name">Image
                            <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 {{ $errors->has('image') ? ' has-error' : '' }}">

                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image"
                                             @if(Auth::user()->adresse==null)
                                                 src="{{asset('front/img/profile.png')}}"
                                             @else
                                                 src="{{ asset('uploads/agents') }}/{{Auth::user()->adresse}}"
                                             @endif
                                             style="width:100px;"/><br/>
                                        <input id="upload_image" type="file" name="image"
                                               onchange="PreviewImage('upload_image','id_image');"/>
                                    </td>
                                </tr>
                            </table>
                            @if ($errors->has('image'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('image') }}</strong>
                                                </span>
                            @endif

                        </div>
                    </div>


                    <div class="item form-group">

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>


                </form>
            </div>
        </div>
        <div class="row">
            <div class="card mb-4 col-md-12 col-sm-12">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Mot de passe </h6>
                </div>
                <form id="demo-form2" data-parsley-validate class="card-body"
                      method="post" action="{{route('admin.profil.password')}}" enctype="multipart/form-data">

                    {{ csrf_field() }}
                    <div class="form-group row">


                        <div class="col-md-6">
                            <label for="password" class="col-form-label ">nouveau mot de passe</label>
                            <div class="form-line">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="new-password">
                            </div>

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password-confirm" class="col-form-label">confirmer mot de
                                passe</label>
                            <div class="form-line">
                                <input id="password-confirm" type="password" class="form-control"
                                       name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>


                    </div>


                    <div class="item form-group">

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>


                </form>
            </div>
        </div>


    </div>

@endsection
