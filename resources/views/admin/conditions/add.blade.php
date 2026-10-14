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
                Ajouter Conditions
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Conditions</li>
                <li>Ajouter</li>
            </ol>
        </div>
        <div class="row">
            <div class="card col-12">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Ajouter</h6>
                </div>

                <form method="post" class="card-body row" action="{{route('admin.conditions.store')}}">
                    @csrf
                    <div class="col-12">
                        <label for="name">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required style="width: 400px">
                    </div>
                    <div class="col-xl-6 mt-4">
                        <label for="Formalite_sante">Formalite sante</label>
                        <textarea class="form-control editor" id="Formalite_sante" name="Formalite_sante"></textarea>
                    </div>
                    <div class="col-xl-6 mt-4">
                        <label for="Formalite_police">Formalite police</label>
                        <textarea class="form-control editor" id="Formalite_police" name="Formalite_police"></textarea>
                    </div>
                    <div class="col-xl-6 mt-4">
                        <label for="Formalite_inscription">Formalite d'inscription</label>
                        <textarea class="form-control editor" id="Formalite_inscription" name="Formalite_inscription"
                        ></textarea>
                    </div>
                    <div class="col-xl-6 mt-4">
                        <label for="Formalite_modifiation">Formalite de modifiation</label>
                        <textarea class="form-control editor" id="Formalite_modifiation" name="Formalite_modifiation"
                        ></textarea>
                    </div>
                    <div class="col-xl-6 mt-4">
                        <label for="Formalite_annulation">Formalite d'annulation</label>
                        <textarea class="form-control editor" id="Formalite_annulation" name="Formalite_annulation">
                          </textarea>
                    </div>
                    <div class="col-12 mt-4">
                        <button class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

    </div>


@endsection
@push("script")
    <script src="https://cdn.ckeditor.com/ckeditor5/32.0.0/classic/ckeditor.js"></script>
    <script>
        var editors = document.querySelectorAll('.editor')
        for (let i = 0; i < editors.length; i++) {
            ClassicEditor
                .create(editors[i])
                .catch(error => {
                    console.error(error);
                });
        }
    </script>

@endpush
