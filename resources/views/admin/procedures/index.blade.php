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
        <a href="{{route('admin.procedures.create')}}" class="btn btn-primary px-5 mb-5" style="border-radius: 0">Ajouter</a>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Media</th>
                <th scope="col">Date</th>
                <th scope="col">Action</th>

            </tr>
            </thead>
            <tbody>
            @foreach($procedures as $procedure)
                <tr>
                    <td scope="row">{{$procedure->name}}</td>
                    <td scope="row">
                        <a href="{{ route('admin.procedures.download', $procedure->id) }}" class="btn btn-primary" style="border-radius: 0">Telecharger</a>

                    </td>
                    <td scope="row">
                        {{\Carbon\Carbon::parse($procedure->created_at)->format('d M Y')}}
                    </td>
                    <td class="">
                        <div class="">
                            <form method="post" action="{{route('admin.procedures.destroy')}}">
                                @csrf
                                <input type="hidden" name="procedureid" value="{{$procedure->id}}">
                                <button class="btn btn-danger" style="border-radius: 0">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function () {

    })
</script>
@endpush
