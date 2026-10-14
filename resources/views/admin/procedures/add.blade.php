@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection




@section('content')

    <div class="right_col" role="main">
        <h1 class="text-center my-4">Procedure</h1>
        <form method="post" action="{{route('admin.procedures.store')}}" enctype="multipart/form-data"
              class="container-fluid align-items-center mt-3">
            @csrf
            <div class="row align-items-center">
                <label for="name" class="col-1">Nom</label>
                <input type="text" id="name" name="name" class="form-control col-3" required>
                <label for="media" class="col-1">Media</label>
                <input type="file" id="media" name="media" class="form-control col-3" required>
            </div>
            <div class="row mt-4">
                <div class="col-2">
                    <button class="btn btn-primary col-12 " style="border-radius: 0">Ajouter</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function () {

    })

</script>
@endpush
