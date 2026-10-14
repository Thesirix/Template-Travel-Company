//    -- -----------------------------intl téléphone drapeau----------------- --
$(document).ready(function () {
    var input = document.querySelector("#phone_number");
    window.iti = window.intlTelInput(input, {
        initialCountry: "fr",
        excludeCountries: ["eh"]
    });

    
    $(".iti__selected-flag").children(".iti__flag").addClass("iti__fr");
    $(".iti__selected-flag").children(".iti__flag").removeClass("iti__us");

    $(".iti__flag").on("click", function () {
        setTimeout(function () {
            if (
                $(".iti__selected-flag")
                    .children(".iti__flag")
                    .hasClass("iti__us")
            ) {
                $(".iti__selected-flag")
                    .children(".iti__flag")
                    .removeClass("iti__us");
                $(".iti__selected-flag")
                    .children(".iti__flag")
                    .addClass("iti__fr");
            }
        }, 100);
    });
});
//  -- -----------------------------intl téléphone drapeau----------------- -

// -- -------------------------------------------script de la recherche ajax---------------------------
$(document).ready(function () {
   
    let isEditing = false; // Pour savoir si on est en mode édition

    clearMessageErreur();

    $("#phone_number").on("keypress", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            rechercherClientOuCreer();
        }
    });

    $("#enregistrerClient").on("click", function () {
        const clientId = $("#client_id").val();
        const phoneNumber = window.iti.getNumber();
    
        // Si pas de numéro ou invalide, on arrête
        if (phoneNumber.length === 0) {
            showErrorToast("Veuillez entrer un numéro");
            return;
        }
        if (!validerNumeroTelephone(phoneNumber)) {
            showErrorToast("Le numéro de téléphone est invalide...");
            return;
        }
    
        // Rechercher ou ajouter un nouveau client
        if (!clientId) {
            rechercherClientOuCreer();
        } else {
            if (isEditing) {
                updateClient(clientId);
            } else {
                scrollToNextCard();
            }
        }
    });
    
    

    function rechercherClientOuCreer(skipFoundToast = false) {
        let phoneNumber = window.iti.getNumber();
    
        if (phoneNumber.length === 0) {
            showErrorToast("Veuillez entrer un numéro");
            return;
        }
        if (!validerNumeroTelephone(phoneNumber)) {
            showErrorToast(
                "Le numéro de téléphone est invalide. Assurez-vous qu’il contient au moins 8 chiffres."
            );
            return;
        }
        $(".spinner-overlay").css('display', 'flex');

        $.ajax({
            url: route.searchClient,
            type: "GET",
            data: { phoneNumber: phoneNumber },
            success: function (response) {
                $(".spinner-overlay").css('display', 'none');
                $("#client_info").html("");
    
                if (response.status === 200) {

                    // On affiche la notif uniquement si skipFoundToast est faux
                    if (!skipFoundToast) {
                        showValideToast("Client existant trouvé.");
                    }
                    displayExistingClient(response.client);
                } else if (response.status === 404) {
            
                    addNewClient(phoneNumber);
                }
            },
            error: function (xhr) {
                $(".spinner-overlay").css('display', 'none');
                showErrorToast(serverError(xhr, "Erreur lors de la recherche du client."));
            },
        });
    }
    

    function clearMessageErreur() {
        $("#infoclient-message").html("");
    }

    function validerNumeroTelephone(phoneNumber) {
        const regex = /^(\+)?\d{8,}$/;
        if (!window.iti.isValidNumber()) {
            return false;
        }
        return regex.test(phoneNumber);
    }

    function displayExistingClient(client) {
        // On stocke l’ID du client dans un input caché
        $("#client_info").append(`
            <div>
                <input type="hidden" id="client_id" value="${client.id}">

                <p><strong>Nom :</strong> <span id="lastNameText">${
                    client.lastName
                }</span></p>
                <input type="text" name="lastName" id="lastNameInput" class="form-control d-none" value="${
                    client.lastName
                }">

                <p><strong>Prénom :</strong> <span id="firstNameText">${
                    client.firstName
                }</span></p>
                <input type="text" name="firstName" id="firstNameInput" class="form-control d-none" value="${
                    client.firstName
                }">

                <p><strong>Email :</strong> <span id="emailText">${
                    client.email ?? ""
                }</span></p>
                <input type="email" name="email" id="emailInput" class="form-control d-none" value="${
                    client.email ?? ""
                }">

                <button type="button" class="edit_client btn btn-primary btn-sm mt-2">Edit</button>
            </div>
        `);
    }

    $(document).on("click", ".edit_client", function () {
        isEditing = true;

        // On masque les spans et on affiche les inputs
        $("#lastNameText, #firstNameText, #emailText").addClass("d-none");
        $("#lastNameInput, #firstNameInput, #emailInput").removeClass("d-none");

        //    désactiver le bouton Edit
        $(this).prop("disabled", true).text("En cours d'édition...");
    });

    function addNewClient(phoneNumber) {
        $.ajax({
            url: route.addClient,
            type: "POST",
            data: {
                phoneNumber: phoneNumber,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.status === 200 && response.client) {
                    showSuccessToast("Nouveau client créé avec succès.");
    
                    $("#client_info").html(`
                        <div>
                            <input type="hidden" id="client_id" value="${response.client.id}">
    
                            <div class="mb-3">
                                <label for="lastNameInput"><strong>Nom :</strong></label>
                                <input type="text" name="lastName" id="lastNameInput" 
                                       class="form-control" value="" 
                                       placeholder="Entrez le nom">
                            </div>
    
                            <div class="mb-3">
                                <label for="firstNameInput"><strong>Prénom :</strong></label>
                                <input type="text" name="firstName" id="firstNameInput" 
                                       class="form-control" value="" 
                                       placeholder="Entrez le prénom">
                            </div>
    
                            <div class="mb-3">
                                <label for="emailInput"><strong>Email :</strong></label>
                                <input type="email" name="email" id="emailInput" 
                                       class="form-control" value="" 
                                       placeholder="Entrez l'email">
                            </div>
                        </div>
                    `);
    
                    isEditing = true;
                    // Focus sur le champ Nom
                    $("#lastNameInput").focus();
    
                } else {
                    showErrorToast("Erreur : Impossible d’ajouter le nouveau client.");
                }
            },
            error: function (xhr) {
                showErrorToast(serverError(xhr, "Une erreur est survenue lors de la création du client."));
            },
        });
    }
    
    
    function updateClient(clientId) {
        let phoneNumber = window.iti.getNumber();
        let emailInputValue = $("#emailInput").val().trim();
        let emailValue = emailInputValue === "" ? null : emailInputValue;
    
        let data = {
            firstName: $("#firstNameInput").val(),
            lastName: $("#lastNameInput").val(),
            email: emailValue,
            phoneNumber: phoneNumber,
            _token: $('meta[name="csrf-token"]').attr("content"),
        };
    
        $.ajax({
            type: "PUT",
            url: route.updateClient(clientId),
            data: data,
            success: function (response) {
                if (response.status === 200) {
                    showValideToast("Client modifié avec succès.");

                    isEditing = false;
    
                    rechercherClientOuCreer(true);

                } else {
                    showErrorToast(response.message);
                }
            },
            error: function (xhr) {
                console.error("Erreur lors de la mise à jour", xhr.responseText);
                showErrorToast(serverError(xhr, "Une erreur est survenue lors de la mise à jour du client."));
            },
        });
    }

    function scrollToNextCard() {
        const currentSection = $("#enregistrerClient").closest(".card");
        const nextSection = currentSection.next(".card");

        if (nextSection.length) {
            $("html, body").animate(
                {
                    scrollTop: nextSection.offset().top,
                },
                500
            );
        }
    }
});