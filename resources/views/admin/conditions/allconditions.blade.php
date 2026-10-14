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
                Conditions
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Conditions</li>
            </ol>
        </div>
        <a href="{{route('admin.conditions.create')}}" class="btn btn-primary mb-5">Ajouter</a>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Conditions</h6>
                </div>
                <div class="table-responsive">

                    <table class="table table-flush">
                        <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Formalite sante</th>
                            <th scope="col">Formalite police</th>
                            <th scope="col">Formalite inscription</th>
                            <th scope="col">Formalite modifiation</th>
                            <th scope="col">Formalite annulation</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($conditions as $condition)
                            <tr>
                                <th scope="row">
                                    {{$condition->nom}}
                                </th>
                                <td>{!! $condition->Formalite_sante !!}</td>
                                <td>{!! $condition->Formalite_police!!}</td>
                                <td>{!! $condition->Formalite_inscription!!}</td>
                                <td>{!! $condition->Formalite_modifiation!!}</td>
                                <td>{!! $condition->Formalite_annulation!!} </td>
                                <td>
                                    <a class="btn btn-primary"
                                       href="{{ route('admin.conditions.edit', $condition->id) }}">Modifier</a>
                                    <form method="post" action="{{route('admin.conditions.destroy')}}">
                                        @csrf
                                        <input type="hidden" name="condition_id" value="{{$condition->id}}">
                                        <button class="btn btn-danger btn-sm">Supprimer</button>
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
@section('script')


@endsection
