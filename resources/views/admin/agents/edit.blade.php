@extends('layouts.admin')
@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection
@section('styles')
    <style>
        .premission_table td {
            padding-top: 10px;
            padding-bottom: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Modifier Agent
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Agents</li>
                <li class="breadcrumb-item">Modifier Agent</li>
            </ol>
        </div>
        <form id="demo-form2" class="card-body " method="post" action="{{ route('admin.agents.update', $agent) }}"
            enctype="multipart/form-data">
            {{ csrf_field() }}
            {{ method_field('PUT') }}
            <div class="card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Agent Informations</h6>
                </div>
                <div class="card-body row">
                    <div class="col-xl-4 mt-4">
                        <label for="name">Nom</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ $agent->name }}" required autocomplete="name" autofocus>

                        @error('name')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>

                    <div class="col-xl-4 mt-4">
                        <label for="prenom"> Prénom</label>
                        <input id="prenom" type="text" class="form-control @error('prenom') is-invalid @enderror"
                            name="prenom" value="{{ $agent->prenom }}" required autocomplete="prenom" autofocus>
                        @error('prenom')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="email">Email</label>
                        <div class="form-line">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ $agent->email }}" required autocomplete="email">
                        </div>
                        @error('email')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="phone"> téléphone</label>
                        <div class="form-line">
                            <input id="phone" type="number" class="form-control @error('phone') is-invalid @enderror"
                                name="phone" value="{{ $agent->phone }}" required autocomplete="phone">
                        </div>
                        @error('phone')
                            <p class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </p>
                        @enderror
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="agence"> Agence</label>
                        <select class="form-control" name="agence">
                            @foreach ($agences as $agence)
                                <option value="{{ $agence->id }}" @if ($agence->id == $agent->agence_id) selected @endif>
                                    {{ $agence->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-4 mt-4">
                        <label for="last-name">Image
                            <span class="required">*</span>
                        </label>
                        <div class="{{ $errors->has('image') ? ' has-error' : '' }}">

                            <table>
                                <tr>
                                    <td>
                                        <img id="id_image"
                                            @if ($agent->image == null) src="{{ asset('front/img/profile.png') }}"
                                             @else
                                             src="{{ asset('uploads/agents') }}/{{ $agent->image }}" @endif
                                            style="width:100px;" /><br />
                                        <input id="upload_image" type="file" name="image"
                                            onchange="PreviewImage('upload_image','id_image');" />
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


                    {{-- -------------------superagent-------------- --}}

                    <div class="form-group col-md-4 mt-3">
                        <label class="d-block">Super agent</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_super_agent" name="is_super_agent"
                                value="1" {{ !empty($gent->is_super_agent) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_super_agent">Peut voir toutes les agences</label>
                        </div>
                    </div>

                    {{-- ---------------------------------- --}}


                </div>
            </div>
            <div class="card row " style="margin-top: 50px">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Droits de l'agent</h6>
                </div>

                @php($permissionGroups = app(\App\Support\AgentPermissions::class)->grouped())
                <div class="card-body">
                    @foreach($permissionGroups as $groupLabel => $permissions)
                        <h6 class="text-gray-500 text-uppercase small font-weight-bold mb-3 {{ $loop->first ? '' : 'mt-4' }}">{{ $groupLabel }}</h6>
                        <div class="tc-perms">
                            @forelse($permissions as $label => $permission)
                                <label class="tc-perm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked($agent->hasPermissionTo($permission, 'gent'))>
                                    <span class="tc-perm__box"><i class="fa-solid fa-check"></i></span>
                                    <span>{{ $label }}</span>
                                </label>
                            @empty
                                <span class="text-gray-500">Aucune destination pour l'instant.</span>
                            @endforelse
                        </div>
                    @endforeach
                </div>
                <style>
                    .tc-perms { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
                    .tc-perm { display: flex; align-items: center; gap: 10px; margin: 0; padding: 11px 14px; border: 1px solid var(--tc-border); border-radius: 12px; cursor: pointer; font-weight: 700; color: var(--tc-text-2); transition: border-color .2s, background .2s; }
                    .tc-perm:hover { border-color: var(--tc-border-strong); }
                    .tc-perm input { position: absolute; opacity: 0; }
                    .tc-perm__box { width: 22px; height: 22px; flex-shrink: 0; border-radius: 7px; border: 2px solid var(--tc-border-strong); display: grid; place-items: center; font-size: .7rem; color: transparent; transition: all .2s var(--tc-spring); }
                    .tc-perm input:checked + .tc-perm__box { background: var(--tc-primary); border-color: var(--tc-primary); color: var(--tc-on-primary); transform: scale(1.08); }
                    .tc-perm:has(input:checked) { border-color: rgba(var(--tc-primary-rgb), .45); background: rgba(var(--tc-primary-rgb), .05); color: var(--tc-text); }
                </style>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
@endsection
