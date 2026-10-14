@props(['adultcount', 'enfantcount', 'bebecount'])
<div class="modal fade" id="devisCalculateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Passagers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body d-flex flex-column justify-content-center align-items-center">
                <p class="d-flex align-items-center  mt-5" style="width: 270px" type="adult">
                    <img src="{{asset('front/img/moins.png')}}" target=".moins-adult"
                         class="devis-moins  mr-4" style="width: 20px">
                    <span style="font-size: 18px" class="devis-adultcount nbtext mr-4">0</span>
                    <img src="{{asset('front/img/plus.png')}}" target=".plus-adult"
                         style="width: 20px" class="devis-plus  mr-4">
                    <span style="font-size: 17px">Adultes</span>
                </p>

                <p class="d-flex align-items-center  mt-3 enfant-part" style="width: 270px; " type="enfant">
                    <img src="{{asset('front/img/moins.png')}}" target=".moins-enfant"
                         class="devis-moins mr-4" style="width: 20px">
                    <span style="font-size: 18px" class="devis-enfantcount nbtext mr-4">0</span>
                    <img src="{{asset('front/img/plus.png')}}" target=".plus-enfant"
                         style="width: 20px" class="devis-plus  mr-4">
                    <span style="font-size: 17px">Enfant (2 - 11 ans)</span>
                </p>
                <p class="d-flex align-items-center  mt-3" style="width: 270px" type="bebe">
                    <img src="{{asset('front/img/moins.png')}}" target=".moins-bebe"
                         class="devis-moins mr-4" style="width: 20px">
                    <span style="font-size: 18px" class="devis-bebecount nbtext mr-4">0</span>
                    <img src="{{asset('front/img/plus.png')}}" target=".plus-bebe" style="width: 20px"
                         class="devis-plus  mr-4">
                    <span style="font-size: 17px">Bebe (< 2 ans)</span>
                </p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

@push("script")
<script>
    // calculer devis
    $(document).on('click', '.CalculDevisButton', function () {
        $('#devisCalculateModal').modal('show')
    })

    ////default devis
    $(document).ready(function () {
            
        for (let i = 0; i < {{$adultcount}}; i++) {
            $(".plus-adult").trigger("click");
            console.log({{$adultcount}} + " adult clicked")
        }
        for (let i = 0; i < {{$enfantcount}}; i++) {
            $(".plus-enfant").trigger("click");
            console.log({{$enfantcount}} + " enfant")
        }
        for (let i = 0; i < {{$bebecount}}; i++) {
            $(".plus-bebe").trigger("click");
            console.log({{$bebecount}} + " bebe")
        }
        ///add passager in devis
        $('.devis-plus').click(function () {
            console.log($($(this).attr('target')))
            $($(this).attr('target')).trigger("click")
        })

        //remove passager in devis
        $('.devis-moins').click(function () {
            console.log($(this).attr('target'))
            $($(this).attr('target')).trigger("click")
        })
    })

</script>

@endpush
