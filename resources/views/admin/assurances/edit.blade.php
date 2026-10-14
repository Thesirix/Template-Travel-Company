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
                Modifier Assurance
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Assurances</li>
                <li>Modifier</li>
            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Modifier</h6>
                </div>
                <form method="post" class="card-body d-flex flex-wrap"  action="{{route('admin.assurances.update', $assurence->id)}}">
                    @csrf
                    <div class="col-4">
                        <label for="type_assu">Destination</label>
                        <select class="form-control" id="type_assu" name="type_assu" required>
                            <option value="{{ \App\Models\Insurance::ALL_DESTINATIONS }}" @selected(old('type_assu', $assurence->type) === \App\Models\Insurance::ALL_DESTINATIONS)>Toutes les destinations</option>
                            @foreach($brand->destinations() as $item)
                                @php($countryName = $item->country?->nom ?? $item->name)
                                <option value="{{ $countryName }}" @selected(strcasecmp((string) old('type_assu', $assurence->type), $countryName) === 0)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <label for="name">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required
                        value="{{$assurence->name}}">
                    </div>
                    <div class="col-4 ">
                        <label for="Formalite_police">Prix</label>
                        <input type="number" class="form-control" id="Prix" name="Prix" rows="3"
                               value="{{$assurence->prix}}"/>
                    </div>
                    <div class="col-8 mt-4">
                        <label for="Formalite_sante">Description</label>
                        <textarea class="form-control" id="Description" name="Description" rows="3">{{$assurence->description}}</textarea>
                    </div>

                    <div class="col-12 mt-4">
                        <button class="btn btn-primary">Modifier</button>
                    </div>
                    <div class="col-12 mt-4">
                        @if ($errors->any())
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="text-danger">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </form>
            </div>
        </div>

    </div>


@endsection
@section('script')


@endsection
