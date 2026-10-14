@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection




@section('content')

    <div class="right_col" role="main">
        <h1 class="text-center my-4">To do list</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addtodolist">Ajouter</button>
        <table class="table table-bordered mt-4">
            <thead>
            <tr>
                <th scope="col">Gent</th>
                <th scope="col">Note</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($todolists as $todolist)
                <tr>
                    <td>{{$todolist->gent->name}} {{$todolist->gent->prenom}}</td>
                    <td>{{$todolist->text}}</td>
                    <td>
                        <form method="post" class="align-items-center" action="{{route('admin.taches.destroy')}}">
                            @csrf
                            <input type="hidden" name="id" value="{{$todolist->id}}">
                            <button class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('script')
<div class="modal fade" id="addtodolist" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{route('admin.taches.store')}}" class="modal-content">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row align-items-center">
                    <label for="gent" class="col-4">Gent</label>
                    <select class="form-control col-8" name="gent" id="gent">
                        @foreach($gents as $gent)
                            <option value="{{$gent->id}}">{{$gent->name}} {{$gent->prenom}}</option>
                        @endforeach
                    </select>
                    <label for="Note" class="col-4 mt-4">Note</label>
                    <input type="text" id="Note" name="Note" class="form-control col-8 mt-4" required>
                </div>

            </div>
            <div class="modal-footer border-top-0" style="width: 150px">
                <button class="btn btn-primary  " style="border-radius: 0;">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {

    })

</script>
@endpush
