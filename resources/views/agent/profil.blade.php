@extends('layouts.admin')

@section('SideBar')
    <x-side-bar/>
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    Profile
                </h6>
            </div>
            <div class="card-body">
                <div class="item form-group">
                    <div class="col-md-6 col-sm-6 {{ $errors->has('prenom') ? ' has-error' : '' }}">
                        Prénom : {{ $user->prenom }}
                    </div>
                </div>

                <div class="item form-group">
                    <div class="col-md-6 col-sm-6 {{ $errors->has('name') ? ' has-error' : '' }}">
                        Nom : {{ $user->name }}
                    </div>
                </div>
                <div class="item form-group">
                    <div class="col-md-6 col-sm-6 {{ $errors->has('name') ? ' has-error' : '' }}">
                        Téléphone : {{ $user->phone }}
                    </div>
                </div>
                <div class="item form-group">
                    <div class="col-md-6 col-sm-6 {{ $errors->has('name') ? ' has-error' : '' }}">
                        Email : {{ $user->email }}
                    </div>
                </div>
                <div class="item form-group">
                    <div class="col-md-6 col-sm-6 {{ $errors->has('image') ? ' has-error' : '' }}">
                        <table>
                            <tr>
                                <td>
                                    <img id="id_image"
                                         @if(Auth::user()->image==null)
                                             src="{{asset('front/img/profile.png')}}"
                                         @else
                                             src="{{ asset('uploads/agents') }}/{{Auth::user()->image}}"
                                         @endif
                                         style="width:100px;"/><br/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
