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
                Modifier Agent Password
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Agents</li>
                <li class="breadcrumb-item">Modifier Agent Password</li>
            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Modifier Password</h6>
                </div>
                <form id="demo-form2" class="card-body row" method="post"
                      action="{{route('admin.agents.password.update',$agent)}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="col-xl-4 mt-4">
                        <label for="password">nouveau mot de passe</label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror" name="password" required
                               autocomplete="new-password">

                        @error('password')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="password-confirm">confirmer mot de passe</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                    <div class="col-12 mt-4 ">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
