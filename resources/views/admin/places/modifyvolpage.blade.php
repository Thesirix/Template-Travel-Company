@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')

    <div class="right_col" role="main">
        <form method="post" action="{{route('admin.places.update')}}" class="x_panel">
            @csrf
            <input type="hidden" name="volid" value="{{$vol->id}}">
            <div class="row mb-5">
                <div class="col-12">
                    <p class="text-center" style="font-size: 30px;color: #ff681e">Modifier vol</p>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-xl-1">
                    <label for="company" style="font-size: 17px">Companie</label>
                </div>
                <div class="col-xl-3">
                    <input type="text" id="company" class="form-control" readonly="readonly"
                           value="{{$vol->company->nom}}"/>
                </div>
                <div class="col-xl-1">
                    <label for="Pnr" style="font-size: 17px">Pnr</label>
                </div>
                <div class="col-xl-3">
                    <input type="text" id="Pnr" class="form-control" readonly="readonly" value="{{$vol->pnr}}"/>
                </div>
                <div class="col-xl-1">
                    <label for="date" style="font-size: 17px">Date</label>
                </div>
                <div class="col-xl-3">
                    <input type="text" id="date" class="form-control" readonly="readonly"
                           value="{{$vol->d}}-{{$vol->m}}-{{$vol->y}}"/>
                </div>
            </div>
            <div class="row align-items-center mt-4">
                <div class="col-xl-1">
                    <label for="total" style="font-size: 17px">Place total</label>
                </div>
                <div class="col-xl-3">
                    <input type="number" name="placetotal" id="total" class="form-control" value="{{$vol->place_total}}" min="0"/>
                </div>
                <div class="col-xl-1">
                    <label for="disponsible" style="font-size: 17px">Place disponsible</label>
                </div>
                <div class="col-xl-3">
                    <input type="number" name="placedisponsible" id="disponsible" class="form-control" value="{{$vol->place_disponible}}" min="0"/>
                </div>
                <div class="col-xl-1">
                    <label for="reserver" style="font-size: 17px">Place reserver</label>
                </div>
                <div class="col-xl-3">
                    <input type="number" name="placereserver" id="reserver" class="form-control" value="{{$vol->place_reserver}}" min="0"/>
                </div>
            </div>
            <div class="col-xl-12 mt-4">
                <button class="btn text-light" style="background-color: #ff681e">Modifier</button>
            </div>
        </form>
    </div>






@endsection

@push('script')


<script>
    $(document).ready(function () {

        $('#total').change(function () {
            $total = $(this)
            $reserver = $('#reserver')
            $result=$total.val() - $reserver.val()
            $('#disponsible').val($result)
        })
        $('#disponsible').change(function () {
            $disponsible = $(this)
            $reserver = $('#reserver')
            $result=parseInt($disponsible.val()) + parseInt($reserver.val())
            $('#total').val($result)
        })
        $('#reserver').change(function () {
            $reserver = $(this)
            $total=$('#total')
            $result=parseInt($total.val()) - parseInt($reserver.val())
            $('#disponsible').val($result)
        })
    })
</script>
@endpush
