function updateRemainingAmount(totalPrice,payedAmount,acompteAmount,input) {
    const amount = parseFloat(input.value) || 0; // Parse input value or default to 0
    const remainingAmount = totalPrice - payedAmount - amount + acompteAmount;
    const remainingAmountElements = document.querySelectorAll('.remaining-amount');

    // Update each element
    remainingAmountElements.forEach(element => {
        element.innerText = remainingAmount + " €";
    });
}

$(document).ready(function () {
    $('.add-new-payment').click(function () {
        $('#add-payement-section').show();
    })

    $('.update_payment').click(function () {
        $('.hideacompte' + $(this).attr('acompte_id')).hide()
        $('.showacompte' + $(this).attr('acompte_id')).show()
        $(this).hide()
        $('.save_payment' + $(this).attr('acompte_id')).show()
    })

    $('.delete_payment').click(function () {
        $('#delete_acompte_id').val($(this).attr('acompte_id'))
        $('#warning_modal').modal('show')
    })

    // function updateRemainingAmount(totalPrice,payedAmount,input) {
    //     const amount = parseFloat(input.value) || 0; // Parse input value or default to 0
    //     const remainingAmount = totalPrice - payedAmount - amount;
    //     document.querySelector('.amount-remaining').textContent = remainingAmount.toFixed(2) + " €";
    // }

});
