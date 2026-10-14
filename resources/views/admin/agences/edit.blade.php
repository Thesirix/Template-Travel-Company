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
                <li class="breadcrumb-item">Agences</li>
                <li class="breadcrumb-item">Modifier</li>
            </ol>
        </div>
        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h2 class="m-0 font-weight-bold text-primary">Modifier une agence</h2>
            </div>
            <div class="card-body">
                <form id="demo-form2" data-parsley-validate class="row"
                      method="post" action="{{route('admin.agences.update',$agence)}}"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    {{ csrf_field() }}
                    <div class="col-12 col-md-3">
                        <label for="name">Nom
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('name') ? ' has-error' : '' }}">
                            <input id="name" type="text" class="form-control" name="name"
                                   value="{{ $agence->name }}" required autofocus placeholder="Agence">

                            @if ($errors->has('name'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="phone">Phone <span
                                class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <input id="phone" type="text" class="form-control" name="phone"
                                   value="{{ $agence->phone }}" required placeholder="Telephone">

                            @if ($errors->has('phone'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('phone') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="email">Email <span
                                class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('email') ? ' has-error' : '' }}">
                            <input id="email" type="email" class="form-control" name="email"
                                   value="{{ $agence->email }}" required placeholder="email">
                            @if ($errors->has('email'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="rue">Addrese <span
                                class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('rue') ? ' has-error' : '' }}">
                            <input id="rue" type="text" class="form-control" name="rue"
                                   value="{{ $agence->rue }}" required placeholder="rue">
                            @if ($errors->has('rue'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('rue') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="phone">Addrese
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('ville') ? ' has-error' : '' }}">
                            <input id="ville" type="text" class="form-control" name="ville"
                                   value="{{ $agence->ville }}" required placeholder="ville">
                            @if ($errors->has('ville'))
                                <span class="help-block">
                                                    <strong>{{ $errors->first('ville') }}</strong>
                                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="last-name">Image
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('image') ? ' has-error' : '' }}">
                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image" src="{{ $agence->image }}" style="width:100px;"/><br/>
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
                    <div class="col-12 col-md-3">
                        <label for="is_partner_agency">Partenaire</label>
                        <input type="checkbox" id="is_partner_agency" name="is_partner_agency" value="1"
                               {{ old('is_partner_agency', isset($agence) ? $agence->is_partner_agency : false) ? 'checked' : '' }}
                               class="form-control" style="width: 30px;height: 30px"/>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
