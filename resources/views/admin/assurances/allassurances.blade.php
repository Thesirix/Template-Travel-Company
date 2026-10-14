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
                Assurances
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Assurances</li>
            </ol>
        </div>

        <a href="{{route('admin.assurances.create')}}" class="btn btn-primary mb-5">Ajouter</a>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Assurances</h6>
                </div>
                <div class="table-responsive">

                    <table class="table table-flush">
                        <thead>
                        <tr>
                            <th scope="col">Type</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Description</th>
                            <th scope="col">prix</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assurances as $condition)
                            <tr>
                                <td>{{$condition->type}}</td>
                                <th scope="row">
                                    {{$condition->name}}
                                </th>
                                <td>{{$condition->description}}</td>
                                <td>{{$condition->prix}} €</td>
                                <td class="d-flex">
                                    <a href="{{ route('admin.assurances.edit', $condition->id) }}" class="btn btn-primary btn-sm">Modifier</a>
                                    <form method="post" action="{{route('admin.assurances.destroy')}}">
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
