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
                Ajouter Agent
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Agents</li>
                <li class="breadcrumb-item">Ajouter Agent</li>
            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Ajouter</h6>
                </div>
                <form id="demo-form2" method="post" class="card-body row" action="{{ route('admin.agents.store') }}"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="col-xl-4 mt-4">
                        <label for="name">Nom</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                        @error('name')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>

                    <div class="col-xl-4 mt-4">
                        <label for="prenom"> Prénom</label>
                        <input id="prenom" type="text" class="form-control @error('prenom') is-invalid @enderror"
                            name="prenom" value="{{ old('prenom') }}" required autocomplete="prenom" autofocus>
                        @error('prenom')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email">

                        @error('email')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>

                    <div class="col-xl-4 mt-4">
                        <label for="phone"> téléphone</label>
                        <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                            name="phone" value="{{ old('phone') }}" required autocomplete="phone">
                        @error('phone')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="password">mot de passe</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="new-password">
                        @error('password')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="password-confirm">confirmer mot de passe</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                            required autocomplete="new-password">
                    </div>

                    <div class="col-xl-4 mt-4">
                        <label for="agence"> Agence</label>
                        <select class="form-control" name="agence">
                            @foreach ($agences as $agence)
                                <option value="{{ $agence->id }}">{{ $agence->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-4 mt-4">
                        <label for="contract_start_date">Date de début de contrat</label>
                        <input id="contract_start_date" type="date" name="contract_start_date" class="form-control"
                            value="{{ old('contract_start_date') }}" required>
                    </div>



                    <div class="col-xl-4 mt-4">
                        <label for="last-name">Image <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image" src="{{ asset('front/img/profile.png') }}"
                                            style="width:100px;" /><br />
                                        <input id="upload_image" type="file" name="image"
                                            onchange="PreviewImage('upload_image','id_image');" />
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

                    {{-- --------------------------super agent----------------- --}}
                    <div class="form-group col-md-4 mt-3">
                        <label class="d-block">Super agent</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_super_agent" name="is_super_agent"
                                value="1">
                            <label class="form-check-label" for="is_super_agent">Peut voir toutes les agences</label>
                        </div>
                    </div>
                    {{-- ------------------------------------------------ --}}

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
