$(document).ready(function () {
    $("#createClientRequest").click(function (event) {
        event.preventDefault();
        $(".spinner-overlay").css("display", "flex");
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        // Récupérer les valeurs des champs
        let formData = {
            formule: $("#formule").val(),
            destination: $("#destination").val(),
            departure_city: $("#depart").val(),
            arrived_city: $("#arrival_city").val(),
            departure_date: $("#departure_date").val(),
            return_date: $("#return_date").val(),
            nb_passagers_adultes: $("#nb_passagers_adultes").val(),
            nb_passagers_childs: $("#nb_passagers_childs").val(),
            nb_passagers_babies: $("#nb_passagers_babies").val(),
            demande_bagage: $("#demande_bagage").val(),
            _token: $('meta[name="csrf-token"]').attr("content"),
        };

        if (!formData.departure_date) {
            showErrorToast("Veuillez choisir une date de départ");
            $(".spinner-overlay").css("display", "none");
            return;
        }

        // Requête AJAX requestclient
        $.ajax({
            url: route.addClientRequest,
            type: "POST",
            data: formData,
            success: function (response) {
                console.log("Réponse serveur :", response);
                if (response.status === 200) {
                    showValideToast(response.message);

                    const currentSection = $("#createClientRequest").closest(
                        ".card"
                    );
                    const nextSection = currentSection.next(".card");
                    if (nextSection.length) {
                        $("html, body").animate(
                            { scrollTop: nextSection.offset().top },
                            500
                        );
                    }

                    $(".btn-save-request").prop("disabled", true);
                } else {
                    showErrorToast(response.message);
                }
                $(".spinner-overlay").css("display", "none");
            },
            error: function (xhr) {
                showErrorToast(serverError(xhr, "Erreur lors de l'enregistrement de la demande client."));
                $(".spinner-overlay").css("display", "none");
            },
        });
    });
});