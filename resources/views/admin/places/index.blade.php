@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')

    <div class="right_col" role="main">

        <div class="x_panel">
            <p style="font-size: 30px;color: #ff681e" class="text-center mb-5">Vols</p>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Companie</th>
                    <th scope="col">PNR</th>
                    <th scope="col">Date</th>
                    <th scope="col">Place total</th>
                    <th scope="col">Place disponsible</th>
                    <th scope="col">Place reserver</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($vols as $vol)
                    <tr>
                        <td scope="row">{{$vol->company->nom}}</td>
                        <td>{{$vol->pnr}}</td>
                        <td>{{$vol->d}}-{{$vol->m}}-{{$vol->y}}</td>
                        <td>{{$vol->place_total}}</td>
                        <td>{{$vol->place_disponible}}</td>
                        <td>{{$vol->place_reserver}}</td>
                        <td>
                            <a href="{{route('admin.places.edit', $vol->id)}}" class="btn btn-sm text-light" style="background-color: #ff681e">Modifier</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>






@endsection

@push('script')

<script>
    $(document).ready(function () {

    })
</script>
@endpush
