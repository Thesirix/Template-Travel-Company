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
                Modifier Vol Group
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Modifier Vol Group</li>
            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <form id="demo-form2" data-parsley-validate class="card-body"
                      method="post" action="{{route('admin.groupes-vols.update')}}">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{$volGroup->id}}">
                    <x-admin.volgroup.form :countries="$countries" :volGroup="$volGroup"/>
                </form>
            </div>
        </div>
    </div>

@endsection
