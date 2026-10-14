@props(['vol', 'volRetour', 'typeVoyage', 'durationAller', 'durationRetour', 'adultcount', 'enfantcount', 'bebecount', 'devisTotalPrice', 'changeDate'])
<div id="page1" class="container-fluid mt-4">
    <div class="d-flex flex-wrap justify-content-center align-items-start" style="color: black">
        @if($typeVoyage != 'retour_simple')
            <div class="card d-flex flex-column px-4 pb-3 mt-5 mx-5 "
                 style="margin-top: 100px;border: solid 5px #6777ef;width: 600px">
                <div class="card-header pt-0" style="font-weight: 700;">
                    <div style="height: 30px" class="bg-primary text-center">
                        <h4 class="m-0 font-weight-bold text-light">Aller</h4>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <p class="" style="font-size: 22px;">
                            {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                        </p>
                    </div>
                </div>
                <div class=" card-body  mt-2">
                    <div class="d-flex align-items-center">
                        <div class="mr-5 text-center">
                            <img src="{{asset($vol->company->image)}}" style="height: 40px">
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column align-items-center mr-4"
                                 style="font-weight:700;font-size: 18px;">
                                <div class="mb-0 text-center" style="font-size: 20px;">
                                    <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',0)->first()->airPortDe->abv}}</p>
                                    <p class="mb-0">{{\Carbon\Carbon::parse($vol->heur_depart_aller)->format("H:i")}}</p>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center mr-4">
                                <p class="mb-0">
                                    {{$durationAller->format('H').' h '.$durationAller->format('i').' min'}}
                                </p>
                                <img src="{{asset('sys/right-arrow.png')}}"
                                     style="width: 70px;height: 20px">
                                <p>@if(count($vol->escaliers->where('type',0))>1)
                                        {{count($vol->escaliers->where('type',0))}}
                                        Escales
                                    @else
                                        Direct
                                    @endif</p>
                            </div>
                            <div class="d-flex flex-column align-items-center mr-4"
                                 style="font-weight:700;font-size: 18px;">
                                <div class="mb-0" style="font-size: 20px;">
                                    <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',0)->last()->airPortA->abv}}</p>
                                    <p class="mb-0">{{\Carbon\Carbon::parse($vol->heur_arriver_aller)->format('H:i')}}</p>
                                </div>
                                
                            </div>

                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-around align-items-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{asset('sys/luggage.png')}}" style="height: 30px"/>
                            <p class="mt-3 ml-3" style="font-weight:700;font-size: 17px;color: #53CE7C">
                                {{$vol->soute_num}} x {{$vol->bagage}} Kgs Soute
                            </p>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{asset('sys/back-pack.png')}}" style="height: 30px"/>
                            <p class="mt-3 ml-3" style="font-weight:700;font-size: 17px;color: #53CE7C">
                                {{$vol->bg_hand}} Kgs cabine
                            </p>
                        </div>
                    </div>
                    @if(count($vol->escaliers->where('type',0))>1)
                        @foreach($vol->escaliers->where('type',0) as $escalier)
                            
                            <div class="mt-5 d-flex mr-4">
                                <div style="width: 150px">
                                    <img src="{{asset($vol->company->image)}}" style="height: 40px"
                                         class="mr-3">
                                    <p>{{$escalier->model}}</p>
                                </div>
                                <div>
                                    
                                    {{--                                                style="color: #0b97c4;font-size: 17px;">--}}

<table class="table table-borderless" style="color: black">
                                        <tr>
                                            <td class="font-weight-bold"
                                                style="color: #0b97c4;font-size: 17px;">
                                                {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d M Y')}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Départ</td>
                                            <td class="font-weight-bold">{{$escalier->heure_depart}}</td>
                                            <td class="text-secondary font-weight-bold">{{$escalier->airPortDe->abv}}</td>
                                        </tr>
                                        <tr>
                                            <td>Arrivée</td>
                                            <td class="font-weight-bold">{{$escalier->heure_arriver}}</td>
                                            <td class="text-secondary font-weight-bold">{{$escalier->airPortA->abv}}</td>
                                        </tr>
                                        <tr>
                                            <td>Temps vol</td>
                                            <td class="font-weight-bold">{{$escalier->temp}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="card-footer pb-0 d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1"
                            validationNum="@if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))5@else 4 @endif"
                            id="page1-validation-check1" next="page1-validation-check2">Valider
                    </button>
                </div>
            </div>
        @endif
        @if($typeVoyage != 'aller_simple')
            @if($typeVoyage=='aller_retour_same_vol')
                <div class="card d-flex flex-column px-4 pb-3 mt-5 mx-5"
                     style="border: solid 5px #6777ef;width: 600px">
                    <div class="card-header  pt-0" style="font-weight: 700">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h4 class="m-0 font-weight-bold text-light">Retour</h4>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <p class="" style="font-size: 22px;">
                                {{\Carbon\Carbon::parse($vol->date_retour)->format('d/m/Y')}}
                            </p>
                        </div>
                    </div>
                    <div class="card-body  mt-2">
                        <div class="d-flex">
                            <div class="mr-5 text-center">
                                <img src="{{asset($vol->company->image)}}" style="height: 40px">
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-size: 20px;font-weight: 700">
                                    <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',1)->first()->airPortDe->abv}}</p>
                                    <p
                                        style="font-size: 22px;">{{\Carbon\Carbon::parse($vol->heur_depart_retour)->format("H:i")}}</p>

</div>
                                <div class="d-flex flex-column align-items-center mr-4">
                                    <p class="mb-0">
                                        @if($volRetour)
                                            {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                        @else
                                            {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                        @endif
                                    </p>
                                    <img src="{{asset('sys/right-arrow.png')}}"
                                         style="width: 70px;height: 20px">
                                    <p>@if(count($vol->escaliers->where('type',1))>1)
                                            {{count($vol->escaliers->where('type',1))}}
                                            Escales
                                        @else
                                            Direct
                                        @endif</p>
                                </div>
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-size: 20px;font-weight: 700">
                                    <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',1)->last()->airPortA->abv}}</p>
                                    <p style="font-size: 22px;">{{\Carbon\Carbon::parse($vol->heur_arriver_retour)->format("H:i")}}</p>

</div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-around align-items-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/luggage.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$vol->soute_num}} x {{$vol->bagage}} Kgs Soute
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/back-pack.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$vol->bg_hand}} Kgs cabine
                                </p>
                            </div>
                        </div>
                        @if(count($vol->escaliers->where('type',1))>1)
                            @foreach($vol->escaliers->where('type',1) as $escalier)
                                
                                <div class="d-flex mt-5">
                                    <div style="width: 150px">
                                        <img src="{{asset($vol->company->image)}}" style="height: 40px"
                                             class="mr-3">
                                        <p>{{$escalier->model}}</p>
                                    </div>
                                    <div>
                                        <table class="table table-borderless" style="color: black">
                                            <tr>
                                                <td class="font-weight-bold"
                                                    style="color: #0b97c4;font-size: 17px;">
                                                    {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d M Y')}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Départ</td>
                                                <td class="font-weight-bold">{{$escalier->heure_depart}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortDe->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Arrivée</td>
                                                <td class="font-weight-bold">{{$escalier->heure_arriver}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortA->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Temps vol</td>
                                                <td class="font-weight-bold">{{$escalier->temp}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                    <div class="card-footer pb-0 d-flex justify-content-center">
                        @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="5"
                                    id="page1-validation-check2" next="page1-validation-check3">Valider
                            </button>
                        @elseif($typeVoyage=='retour_simple')
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="4"
                                    id="page1-validation-check1" next="page1-validation-check2">Valider
                            </button>
                        @endif
                    </div>
                </div>
            @elseif($typeVoyage=='aller_retour_diffrent_vol')
                <div class="card d-flex flex-column px-4 pb-3 mt-5 mx-5 "
                     style="margin-top: 100px;border: solid 5px #6777ef;width: 600px">
                    <div class="card-header pt-0" style="font-weight: 700;">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h4 class="m-0 font-weight-bold text-light">Retour</h4>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <p class="" style="font-size: 22px;">
                                {{\Carbon\Carbon::parse($volRetour->date_aller)->format("d/m/Y")}}
                            </p>
                        </div>
                    </div>
                    <div class=" card-body  mt-2">
                        <div class="d-flex align-items-center">
                            <div class="mr-5 text-center">
                                <img src="{{asset($volRetour->company->image)}}" style="height: 40px">
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-weight:700;font-size: 18px;">
                                    <div class="mb-0 text-center" style="font-size: 20px;">
                                        <p class="text-secondary mb-0"> {{$volRetour->escaliers->where('type',0)->first()->airPortDe->abv}}</p>
                                        <p class="mb-0">{{\Carbon\Carbon::parse($volRetour->heur_depart_aller)->format("H:i")}}</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-center mr-4">
                                    <p class="mb-0">
                                        {{$durationRetour->format('H').' h '.$durationRetour->format('i').' min'}}
                                    </p>
                                    <img src="{{asset('sys/right-arrow.png')}}"
                                         style="width: 70px;height: 20px">
                                    <p>@if(count($volRetour->escaliers->where('type',0))>1)
                                            {{count($volRetour->escaliers->where('type',0))}}
                                            Escales
                                        @else
                                            Direct
                                        @endif</p>
                                </div>
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-weight:700;font-size: 18px;">
                                    <div class="mb-0" style="font-size: 20px;">
                                        <p class="text-secondary mb-0"> {{$volRetour->escaliers->where('type',0)->last()->airPortA->abv}}</p>
                                        <p class="mb-0">{{\Carbon\Carbon::parse($volRetour->heur_arriver_aller)->format('H:i')}}</p>
                                    </div>
                                    
                                </div>

                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-around align-items-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/luggage.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$volRetour->soute_num}} x {{$volRetour->bagage}} Kgs Soute
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/back-pack.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$volRetour->bg_hand}} Kgs cabine
                                </p>
                            </div>
                        </div>
                        @if(count($volRetour->escaliers->where('type',0))>1)
                            @foreach($volRetour->escaliers->where('type',0) as $escalier)
                                
                                <div class="mt-5 d-flex mr-4">
                                    <div style="width: 150px">
                                        <img src="{{asset($volRetour->company->image)}}"
                                             style="height: 40px"
                                             class="mr-3">
                                        <p>{{$escalier->model}}</p>
                                    </div>
                                    <div>
                                        <table class="table table-borderless" style="color: black">
                                            <tr>
                                                <td class="font-weight-bold"
                                                    style="color: #0b97c4;font-size: 17px;">
                                                    {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d M Y')}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Départ</td>
                                                <td class="font-weight-bold">{{$escalier->heure_depart}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortDe->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Arrivée</td>
                                                <td class="font-weight-bold">{{$escalier->heure_arriver}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortA->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Temps vol</td>
                                                <td class="font-weight-bold">{{$escalier->temp}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="card-footer pb-0 d-flex justify-content-center">
                        @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="5"
                                    id="page1-validation-check2" next="page1-validation-check3">Valider
                            </button>
                        @elseif($typeVoyage=='retour_simple')
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="4"
                                    id="page1-validation-check1" next="page1-validation-check2">Valider
                            </button>
                        @endif
                    </div>
                </div>
            @elseif($typeVoyage=='retour_simple')
                <div class="card d-flex flex-column px-4 pb-3 mt-5 mx-5 "
                     style="margin-top: 100px;border: solid 5px #6777ef;width: 600px">
                    <div class="card-header pt-0" style="font-weight: 700;">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h4 class="m-0 font-weight-bold text-light">Retour</h4>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <p class="" style="font-size: 22px;">
                                {{\Carbon\Carbon::parse($vol->date_aller)->format("d/m/Y")}}
                            </p>
                        </div>
                    </div>
                    <div class=" card-body  mt-2">
                        <div class="d-flex align-items-center">
                            <div class="mr-5 text-center">
                                <img src="{{asset($vol->company->image)}}" style="height: 40px">
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-weight:700;font-size: 18px;">
                                    <div class="mb-0 text-center" style="font-size: 20px;">
                                        <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',0)->first()->airPortDe->abv}}</p>
                                        <p class="mb-0">{{\Carbon\Carbon::parse($vol->heur_depart_aller)->format("H:i")}}</p>
                                    </div>

</div>
                                <div class="d-flex flex-column align-items-center mr-4">
                                    <p class="mb-0">

{{$durationAller->format('H').' h '.$durationAller->format('i').' min'}}
                                    </p>
                                    <img src="{{asset('sys/right-arrow.png')}}"
                                         style="width: 70px;height: 20px">
                                    <p>@if(count($vol->escaliers->where('type',0))>1)
                                            {{count($vol->escaliers->where('type',0))}}
                                            Escales
                                        @else
                                            Direct
                                        @endif</p>
                                </div>
                                <div class="d-flex flex-column align-items-center mr-4"
                                     style="font-weight:700;font-size: 18px;">
                                    <div class="mb-0" style="font-size: 20px;">
                                        <p class="text-secondary mb-0"> {{$vol->escaliers->where('type',0)->last()->airPortA->abv}}</p>
                                        <p class="mb-0">{{\Carbon\Carbon::parse($vol->heur_arriver_aller)->format('H:i')}}</p>
                                    </div>
                                    
                                </div>

                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-around align-items-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/luggage.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$vol->soute_num}} x {{$vol->bagage}} Kgs Soute
                                </p>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('sys/back-pack.png')}}" style="height: 30px"/>
                                <p class="mt-3 ml-3"
                                   style="font-weight:700;font-size: 17px;color: #53CE7C">
                                    {{$vol->bg_hand}} Kgs cabine
                                </p>
                            </div>
                        </div>
                        @if(count($vol->escaliers->where('type',0))>1)
                            @foreach($vol->escaliers->where('type',0) as $escalier)
                                
                                <div class="mt-5 d-flex mr-4">
                                    <div style="width: 150px">
                                        <img src="{{asset($vol->company->image)}}" style="height: 40px"
                                             class="mr-3">
                                        <p>{{$escalier->model}}</p>
                                    </div>
                                    <div>
                                        
                                        {{--                                                style="color: #0b97c4;font-size: 17px;">--}}

<table class="table table-borderless" style="color: black">
                                            <tr>
                                                <td class="font-weight-bold"
                                                    style="color: #0b97c4;font-size: 17px;">
                                                    {{\Carbon\Carbon::parse($escalier->date_depart)->translatedFormat('D d M Y')}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Départ</td>
                                                <td class="font-weight-bold">{{$escalier->heure_depart}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortDe->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Arrivée</td>
                                                <td class="font-weight-bold">{{$escalier->heure_arriver}}</td>
                                                <td class="text-secondary font-weight-bold">{{$escalier->airPortA->abv}}</td>
                                            </tr>
                                            <tr>
                                                <td>Temps vol</td>
                                                <td class="font-weight-bold">{{$escalier->temp}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="card-footer pb-0 d-flex justify-content-center">
                        @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="5"
                                    id="page1-validation-check2" next="page1-validation-check3">Valider
                            </button>
                        @elseif($typeVoyage=='retour_simple')
                            <button type="button" class="btn btn-secondary page-validation-check"
                                    pageNum="1" validationNum="4"
                                    id="page1-validation-check1" next="page1-validation-check2">Valider
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
    <div class="d-flex align-items-center flex-column">
        
        <div class="card mt-5 pt-0 pb-3" style="border: solid 5px #6777ef;">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h4 class="m-0 font-weight-bold text-light">
                        Ce prix comprend
                    </h4>
                </div>
            </div>
            <div class="card-body ">
                {!! $vol->prix_comprend !!}
            </div>
            <div class="card-footer pb-0 d-flex justify-content-center">
                @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="5"
                            id="page1-validation-check3" next="page1-validation-check4">Valider
                    </button>
                @else
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="4"
                            id="page1-validation-check2" next="page1-validation-check3">Valider
                    </button>
                @endif
            </div>
        </div>
        <div class="card mt-5 pt-0 pb-3" style="border: solid 5px #6777ef;">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h4 class="m-0 font-weight-bold text-light">
                        Ce prix ne comprend pas
                    </h4>
                </div>
            </div>
            <div class="card-body ">
                {!! $vol->prix_comprend_pas !!}
            </div>
            <div class="card-footer pb-0 d-flex justify-content-center">
                @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="5"
                            id="page1-validation-check4" next="page1-validation-check5">Valider
                    </button>
                @else
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="4"
                            id="page1-validation-check3" next="page1-validation-check4">Valider
                    </button>
                @endif
            </div>
        </div>
        <div class="card mt-5 pt-0 pb-3" style="border: solid 5px #6777ef;">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h4 class="m-0 font-weight-bold text-light">
                        Devis
                    </h4>
                </div>
            </div>
            <div class="card-body  "
                 style="color:black;font-size: 22px;width: 100%">
                <div class="d-flex align-items-center justify-content-center">
                    @if(\Illuminate\Support\Facades\Auth::guard('gent')->user()->agence->is_partner_agency)
                        @if($typeVoyage=='aller_retour_diffrent_vol')
                            <p style="cursor: pointer" class=" mx-5">
                                <span class="devis-adultcount">{{$adultcount}}</span>
                                adulte {{number_format(($vol->prix_vente_adult+$volRetour->prix_vente_adult),0)}}
                                €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" class="-bold mx-5">
                                <span class="devis-enfantcount">{{$enfantcount}}</span>
                                enfant {{number_format(($vol->prix_vente_enfant+$volRetour->prix_vente_enfant),0)}}
                                €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" class=" mx-5">
                                <span class="devis-bebecount">{{$bebecount}}</span>
                                bebe {{number_format(($vol->prix_vente_bebe+$volRetour->prix_vente_bebe),0)}}
                                €
                            </p>
                        @else
                            <p style="cursor: pointer" class=" mx-5">
                                <span class="devis-adultcount">{{$adultcount}}</span>
                                adulte
                                {{number_format($vol->prix_vente_adult,0)}} €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" class="-bold mx-5">
                                <span class="devis-enfantcount">{{$enfantcount}}</span>
                                enfant {{number_format($vol->prix_vente_enfant,0)}}€
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" class="mx-5">
                                <span class="devis-bebecount">{{$bebecount}}</span>
                                bebe {{number_format($vol->prix_vente_bebe,0)}}€
                            </p>
                        @endif
                    @else
                        @if($typeVoyage=='aller_retour_diffrent_vol')
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class=" mx-5"
                               title="Prix d'achat :{{$vol->prix_achat_adult+$volRetour->prix_achat_adult}} €">
                                <span class="devis-adultcount">{{$adultcount}}</span>
                                adulte {{number_format(($vol->prix_vente_adult+$volRetour->prix_vente_adult),0)}}
                                €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class="-bold mx-5"
                               title="Prix d'achat : {{$vol->prix_achat_enfant+$volRetour->prix_achat_enfant}} €">
                                <span class="devis-enfantcount">{{$enfantcount}}</span>
                                enfant {{number_format(($vol->prix_vente_enfant+$volRetour->prix_vente_enfant),0)}}
                                €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class=" mx-5"
                               title="Prix d'achat : {{$vol->prix_achat_bebe+$volRetour->prix_achat_bebe}} €">
                                <span class="devis-bebecount">{{$bebecount}}</span>
                                bebe {{number_format(($vol->prix_vente_bebe+$volRetour->prix_vente_bebe),0)}}
                                €
                            </p>

                        @else
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class=" mx-5"
                               title="Prix d'achat :{{$vol->prix_achat_adult}} €">
                                <span class="devis-adultcount">{{$adultcount}}</span>
                                adulte
                                {{number_format($vol->prix_vente_adult,0)}} €
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class="-bold mx-5"
                               title="Prix d'achat : {{$vol->prix_achat_enfant}} €">
                                <span class="devis-enfantcount">{{$enfantcount}}</span>
                                enfant {{number_format($vol->prix_vente_enfant,0)}}€
                            </p>
                            <p>+</p>
                            <p style="cursor: pointer" data-bs-toggle="tooltip" data-bs-placement="right"
                               class=" mx-5"
                               title="Prix d'achat : {{$vol->prix_achat_bebe}} €">
                                <span class="devis-bebecount">{{$bebecount}}</span>
                                bebe {{number_format($vol->prix_vente_bebe,0)}}€
                            </p>
                        @endif
                    @endif
                </div>
                <p class="mb-0 text-center devis-prix-total">{{$devisTotalPrice}} €</p>
            </div>
            <div class="card-footer pb-0 d-flex justify-content-around">
                @if(!$changeDate)
                    <button type="button" id="vol_num_{{$vol->id}}"
                            priceAdult="{{floor($vol->prix_vente_adult)}}"
                            priceBebe="{{floor($vol->prix_vente_bebe)}}" vol_id="{{$vol->id}}"
                            class="btn btn-secondary CalculDevisButton">
                        Calculer
                    </button>
                @endif
                @if(in_array($typeVoyage,['aller_retour_same_vol','aller_retour_diffrent_vol']))
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="5"
                            id="page1-validation-check5" next="page2-validation-check1">Valider
                    </button>
                @else
                    <button type="button" class="btn btn-secondary page-validation-check"
                            pageNum="1" validationNum="4"
                            id="page1-validation-check4" next="page2-validation-check1">Valider
                    </button>
                @endif
            </div>

        </div>
    </div>
</div>

<x-agent.vols.inscription.page1.components.devis-calculate-modal :adultcount="$adultcount" :enfantcount="$enfantcount"
                                                                :bebecount="$bebecount"/>

