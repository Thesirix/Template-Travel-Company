@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')

    <style>

        /*******menue*********/


        .formlist {
            background-color: rgb(34, 54, 107);
            cursor: pointer;
            padding: 10px 10px 10px 10px;
            max-width: 200px;
            top: 60px;
            height: 100vh;
            position: sticky !important
        }

        .forumitem p {
            color: white !important;
        }

    </style>
    <div class="right_col" role="main">

        <div class="x_panel">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Agent</th>
                    <th scope="col">Client nom</th>
                    <th scope="col">Adresse</th>
                    <th scope="col">Email</th>
                    <th scope="col">Tele</th>
                    <th scope="col">Montant</th>
                    <th scope="col">text</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payements as $payement)
                    <tr>
                        <td>{{$payement->user->name.' '.$payement->user->prenom}}</td>
                        <td>{{$payement->fullname}}</td>
                        <td>{{$payement->adresse.' '.$payement->ville.' '.$payement->zip}}</td>
                        <td>{{$payement->email}}</td>
                        <td>{{$payement->telephone}}</td>
                        <td>{{$payement->price}}</td>
                        <td>{{$payement->text}}</td>
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

        $('#acceptpaypolicy').change(function () {
            if (this.checked) {
                $('#paynow').removeAttr('disabled');
            } else {
                $('#paynow').attr('disabled', 'disabled');
            }
        })
    })
</script>
@endpush
