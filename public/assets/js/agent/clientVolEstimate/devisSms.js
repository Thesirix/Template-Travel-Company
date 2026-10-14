    // Bouton  Envoyer SMS
    $("#sendDevisSms").click(function (event) {
        event.preventDefault();

        const devis_departure_date = $("#devis_departure_date").val();
        const devis_return_date = $("#devis_return_date").val();
        let proposed_price = $("#proposed_price").val();
        let client_id = $("#client_id").val();

        // Construire les données du formulaire
        let formData = {
            client_id: client_id,
            Compagnie: $("#Compagnie").val(),
            Installation: $("#Installation").val(),
            Véhicule: $("#Véhicule").val(),
            bagage: $("#bagage-devis").val(),
            depart: $("#depart").val(),
            arrived_city: $("#arrival_city").val(),
            devis_departure_date: devis_departure_date,
            devis_return_date: devis_return_date,
            nb_passagers_adultes: $("#nb_passagers_adultes2").val(),
            nb_passagers_childs: $("#nb_passagers_childs2").val(),
            nb_passagers_babies: $("#nb_passagers_babies2").val(),
            proposed_price: proposed_price,
            formule: $("#formule").val(),
            destination: $("#destination").val(),
            _token: $('meta[name="csrf-token"]').attr("content"),
        };

        if (!client_id) {
            showErrorToast("Veuillez d'abord rechercher un client.");

            return;
        }

        if (!devis_departure_date) {
            showErrorToast("Veuillez entrer la date de départ.");

            return;
        }

        if (!proposed_price) {
            showErrorToast("Veuillez proposer un prix.");

            return;
        }
        scrollToNextCard();
        // Créer le devis
        $.ajax({
            url: route.addDevis,
            type: "POST",
            data: formData,
            success: function (response) {
                if (response.status === 200) {
                    // Récupérer l'ID du devis
                    let estimateId = response.client_vol_estimate_id;
                    console.log("Estimate ID:", estimateId);

                    $("#client_vol_estimate_id").val(estimateId);

                    $("#sendDevisSms").data("estimate-id", estimateId);
                    $("#sendDevisSms").prop("disabled", false);

                    sendSms(estimateId);
                    $(".btn-save-devis").prop("disabled", true);
                } else {
                    showErrorToast("Probleme d'envoie du devis");
                }
            },
            error: function (xhr) {
                showErrorToast(serverError(xhr, "Erreur lors de la création du devis. (Problème Serveur)"));

                console.error(
                    "Erreur lors de la création du devis",
                    xhr.responseText
                );
            },
        });
    });

    // Fonction pour envoyer le SMS
    function sendSms(estimateId) {
        if (!estimateId) {
            showErrorToast(
                "Veuillez d'abord créer un devis valide pour envoyer le SMS."
            );
            return;
        }
        // éviter les clics multiples
        $(".spinner-overlay").css('display', 'flex');
        $("#sendDevisSms").prop("disabled", true).text("Envoi en cours...");
    
        $.ajax({
            url: route.sendDevisSms,
            type: "POST",
            data: {
                client_vol_estimate_id: estimateId,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $(".spinner-overlay").css('display', 'none');
                if (response.code === 200) {
                    showValideToast("SMS envoyé avec succès !");
                    // Désactiver définitivement le bouton après succès
                    $("#sendDevisSms").prop("disabled", true).text("SMS envoyé");
                } else {
                    showErrorToast(response.message);
                    console.error(
                        "Erreur serveur lors de l'envoi du SMS",
                        response
                    );
                    // Réactiver le bouton en cas d'erreur
                    $("#sendDevisSms").prop("disabled", false).text("Envoyer SMS");
                }
            },
            error: function (xhr, status, error) {
                $(".spinner-overlay").css('display', 'none');
                console.error(
                    "Erreur lors de l'envoi du SMS :",
                    xhr.responseText
                );
                showErrorToast(serverError(xhr, "Erreur lors de l'envoi du SMS (réseau ou serveur)."));
                $("#sendDevisSms").prop("disabled", false).text("Envoyer SMS");
            },
        });
    }

    function scrollToNextCard() {
        const currentCard = $("#sendDevisSms").closest(".card");
        const nextCard = currentCard.next(".card");

        if (nextCard.length) {
            $("html, body").animate(
                {
                    scrollTop: nextCard.offset().top,
                },
                500
            );
        }
    }
