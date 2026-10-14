@props(['vol', 'volRetour', 'typeVoyage', 'destination', 'assurences'])
<div id="page4" class="row text-center justify-content-center"
     style="margin-top:100px;display: none">
    <div class="col-6">
        <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
            <div class="card-header pb-3 pt-0">
                <div style="height: 30px" class="bg-primary text-center">
                    <h6 class="m-0 font-weight-bold text-light">
                        Passagers
                    </h6>
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <input type="hidden" id="nbPassagers" class="input" name="nbPassagers"
                       value="0">
                <p class="d-flex align-items-center  mt-5" style="width: 270px" type="adult">
                    <img src="{{asset('front/img/moins.png')}}" target="adultcountinput2"
                         class="moins moins-adult mr-4"
                         style="width: 20px">
                    <span style="font-size: 18px" class="adultcount nbtext mr-4">0</span>
                    <img src="{{asset('front/img/plus.png')}}" target="adultcountinput2"
                         style="width: 20px" class="plus plus-adult mr-4">
                    <span id="adultcount" style="font-size: 17px">Adultes</span>
                    <input type="hidden" id="adultcountinput" class="input" name="adultcountinput"
                           value="0">
                </p>
                <p class="d-flex align-items-center  mt-3" style="width: 270px" type="enfant">
                    <img src="{{asset('front/img/moins.png')}}" target="enfantcountinput2"
                         class="moins moins-enfant mr-4"
                         style="width: 20px">
                    <span style="font-size: 18px" class="enfantcount nbtext mr-4">0</span>
                    <img src="{{asset('front/img/plus.png')}}" target="enfantcountinput2"
                         style="width: 20px"
                         class="plus plus-enfant mr-4">
                    <span id="enfantcount" style="font-size: 17px">Enfant (2 - 11 ans)</span>
                    <input type="hidden" id="enfantcountinput" class="input" name="enfantcountinput"
                           value="0">
                </p>
                
                    <p class="d-flex align-items-center  mt-3" style="width: 270px" type="bebe">
                        <img src="{{asset('front/img/moins.png')}}" target="bebecountinput2"
                             class="moins moins-bebe mr-4" style="width: 20px">
                        <span style="font-size: 18px" class="bebecount nbtext mr-4">0</span>
                        <img src="{{asset('front/img/plus.png')}}" target="bebecountinput2"
                             style="width: 20px"
                             class="plus plus-bebe mr-4">
                        <span style="font-size: 17px">Bebe (< 2 ans)</span>
                        <input type="hidden" id="bebecountinput" max="1" class="input" name="bebecountinput"
                               value="0">
                    </p>
                
            </div>
        </div>
    </div>
    <div class="col-12 d-flex flex-wrap" id="passagersinputs">
    </div>
</div>

<x-agent.common.image-preview-modal/>


