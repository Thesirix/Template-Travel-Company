$(document).ready(function () {
    let outboundFlightChecked = false;
    let returnFlightChecked = false;
// display inscription button on select vols
    $(document).on('change', 'input[name="vol_id"]', function () {
        outboundFlightChecked = true;
        displayInscriptionButton($(this).attr('itineraire'), 'departure');
    });
    $(document).on('change', 'input[name="vol_id_retour"]', function () {
        returnFlightChecked = true;
        displayInscriptionButton($(this).attr('itineraire'), 'return');
    });

    function displayInscriptionButton(checkedFlightItineraire, flightType) {
        if ($isGroupsameItineraire==true) {
            if (flightType === 'departure') {
                $('input[name="vol_id_retour"]').hide();
                const checkedFlightLastAirport = checkedFlightItineraire.trim().split(/\s+/).pop();
                $('input[name="vol_id_retour"]').filter(function () {
                    const firstAirport = $(this).attr('itineraire').split(' ')[0]; // Get the first word of itineraire
                    return firstAirport.toLowerCase() === checkedFlightLastAirport.toLowerCase(); // Compare with lastWord
                }).show();
            } else {
                $('input[name="vol_id"]').hide();
                const checkedFlightFirstAirport = checkedFlightItineraire.trim().split(/\s+/)[0];
                $('input[name="vol_id"]').filter(function () {
                    const lastAirport = $(this).attr('itineraire').split(' ').pop(); // Get the first word of itineraire
                    return checkedFlightFirstAirport.toLowerCase() === lastAirport.toLowerCase(); // Compare with lastWord
                }).show();
            }
        }

        if ($groupType !== 'multi-segment-required' || ($groupType === 'multi-segment-required' && outboundFlightChecked && returnFlightChecked)) {
            $('.dif_vols_inscription_button').removeClass('d-none');
        }
    }

    //End display inscription button on select vols

    // reset the selected vols
    $('#reset').click(function () {
        $('input[name="vol_id"]').prop('checked', false);
        $('input[name="vol_id_retour"]').prop('checked', false);
        $('input[name="vol_id"]').show();
        $('input[name="vol_id_retour"]').show();
        $('.dif_vols_inscription_button').addClass('d-none');
        outboundFlightChecked = false;
        returnFlightChecked = false;
    })

});


$(document).on('mouseenter', '.vol_row', function () {
    $(this).children('td').addClass("table-primary")
})
$(document).on('mouseleave', '.vol_row', function () {
    $(this).children('td').removeClass("table-primary")
})


//////////////Calculate devis
$clickedRow = 0
$(document).on('click', '.CalculDevisButton', function () {
    $clickedRow = $(this).attr('vol_id')

    $('.counter').text(0)
    $('.counterInput').val(0)
    $('#devisCalculateModal').modal('show')
})
$('.calculateDevis').click(function () {
    $inputTarget = 'vol' + $clickedRow + $(this).attr('inputTarget')
    $spanTarget = $(this).attr('spanTarget')
    $type = $(this).attr('typeCalcul')
    $number = parseInt($('#' + $inputTarget).val())
    if ($type == 'mois') {
        if ($number > 0) {
            $number--;
        }
    } else {
        $number++;
    }
    $('#' + $inputTarget).val($number);
    $('#' + $spanTarget).text($number);
    $adultcountinput = parseInt($('#vol' + $clickedRow + 'adultcountinput').val())
    $enfantcountinput = parseInt($('#vol' + $clickedRow + 'enfantcountinput').val())
    $bebecountinput = parseInt($('#vol' + $clickedRow + 'bebecountinput').val())
    $prixTotal = ($('#vol_num_' + $clickedRow).attr('priceadult') * $adultcountinput) + ($('#vol_num_' + $clickedRow).attr('priceenfant') * $enfantcountinput) + ($('#vol_num_' + $clickedRow).attr('pricebebe') * $bebecountinput)
    $('#vol_num_' + $clickedRow).text($adultcountinput + ' ADLT +' + $enfantcountinput + ' ENFT +' + $bebecountinput + ' BB =' + $prixTotal + ' €');

})
//////////////End Calculate devis
