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
                Agents
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Agents</li>
            </ol>
        </div>
        <a href="{{route('admin.agents.create')}}" class="btn btn-primary mb-5">Ajouter</a>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Agents</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-flush">
                        <thead>
                        <tr class="headings">

                            <th class="column-title">Nom</th>
                            <th class="column-title">Prénom</th>
                            <th class="column-title">Email</th>
                            <th class="column-title">Téléphone</th>
                            <th class="column-title">photo</th>
                            <th class="column-title">Agence</th>
                            <th class="column-title no-link last"><span class="nobr">Action</span></th>
                            <th class="bulk-actions" colspan="7">
                                <a class="antoo" style="color:#fff; font-weight:500;">Bulk Actions ( <span
                                        class="action-cnt"> </span> ) <i class="fa fa-chevron-down"></i></a>
                            </th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($agents as $agent)
                            <tr class="even pointer">
                                <td>{{$agent->name}}</td>
                                <td>{{$agent->prenom}}</td>
                                <td>{{$agent->email}}</td>
                                <td>{{$agent->phone}}</td>
                                <td>
                                    <img
                                        @if($agent->image==null)
                                        src="{{asset('front/img/profile.png')}}"
                                        @else
                                        src="{{ asset('uploads/agents') }}/{{$agent->image}}"
                                        @endif
                                        style="width:25px;height:25px"/>
                                </td>
                                <td>{{$agent->agence->name}}</td>
                                <td class=" ">
                                    <a href="{{ route('admin.agents.edit',$agent) }}" class="btn btn-primary"
                                       style="display:inherit;">
                                        Modifier
                                    </a>
                                    <a href="{{ route('admin.agents.password',$agent) }}" class="btn btn-primary"
                                       style="display:inherit;">
                                        password
                                    </a>
                                    <form method="post" action="{{ route('admin.agents.toggle') }}"
                                          style="display:inherit;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="gent_id" value="{{$agent->id}}"/>
                                        @if($agent->state=='disable')
                                            <button type="submit" class="btn btn-primary waves-effect">
                                                Active
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-danger waves-effect">Desactive
                                            </button>
                                        @endif
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
