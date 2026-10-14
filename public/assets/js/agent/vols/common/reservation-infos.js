$(document).ready(function () {

    $('#display-bill-form').click(function () {
        $('#bill-form').show();
        $('#bill-section').hide();
    });
    $('#hide-bill-form').click(function () {
        $('#bill-form').hide();
        $('#bill-section').show();
        $('#billNumber').val($(this).attr('n_acompte'));
    });

    $('#display-agent-form').click(function () {
        $('#agent-form').show();
        $('#agent-section').hide();
    });

    $('.delete-passport-passenger-button').click(function (){
        $('#passenger_id').val($(this).attr('passenger-id'));
        $('.delete-passport-passenger-modal').modal('show');
    });

    $('#delete-passport-passenger-form').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission
        $(".spinner-overlay").css('display', 'flex');

        const form = this;
        const formData = new FormData(form); // Create a FormData object
        const url = form.action; // Get the URL from the form's action attribute
        $('.delete-passport-passenger-modal').modal('hide');

        $.ajax({
            type: 'POST', // Use the POST method
            url: url, // The URL from the form's action attribute
            data: formData, // Send FormData as payload
            processData: false, // Prevent jQuery from processing the FormData object
            contentType: false, // Prevent jQuery from setting the Content-Type header
            success: function (response) {
                // Handle successful response
                if(response === '0001'){
                    $('#operation-success-message').text('L\'image du passeport a été supprimée avec succès.')
                    $('#operation-success-modal').modal('show');
                    const passengerId = formData.get('passenger_id'); // Assuming the input has name="passenger_id"
                    $('#img-preview-'+passengerId).html('');
                }else{
                    $('#operation-failed-message').text('Erreur techique')
                    $('#operation-failed-modal').modal('show');
                }
                $(".spinner-overlay").css('display', 'none');
            },
            error: function (xhr, status, error) {
                // Handle error
                $('#operation-failed-message').text('Erreur techique')
                $('#operation-failed-modal').modal('show');
                console.log(`Error: ${xhr.status} - ${xhr.responseText}`);
                $(".spinner-overlay").css('display', 'none');
            },
        });
    });
});
