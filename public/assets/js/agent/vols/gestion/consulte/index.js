$('.submit-form').submit(function (){
    $(".spinner-overlay").css('display','flex');
})

$('.modify_passager').click(function () {
    $(this).hide();
    $(this).parent().children('.enregister_passager').show()
})

$(document).on('click', '.modify_passager', function () {
    let id = $(this).attr('passager_id')
    $('.input_' + id).removeAttr('readonly')
    $('.input_' + id).removeAttr('disabled')
})

$(document).on('click', '.pasport_file', function () {
    $('#image_preview_mdal_img').attr('src', $(this).attr('src'))
    $('#image_preview_mdal').modal('show')
})

$(document).on('change', '.passportfieldfile ', function (event) {
    $(this).parent().children('.custom-file-upload').css({'background': 'green', 'color': 'white'})
    let forma = event.target.files[0]['name'].toLowerCase().split(".")
    forma = forma[forma.length - 1]
    if (forma == "pdf") {
        $(this).parent().children('.img_preview').html('<embed  src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px;"/>')
    } else if (["jpg", "png", "jpeg"].includes(forma)) {
        $(this).parent().children('.img_preview').html('<img src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px"/>')
    }
})
