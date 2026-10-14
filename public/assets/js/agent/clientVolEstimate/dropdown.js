function syncPassengerValues() {
    const adultCount = document.getElementById("nb_passagers_adultes").value;
    const childCount = document.getElementById("nb_passagers_childs").value;
    const babyCount = document.getElementById("nb_passagers_babies").value;

    document.getElementById("nb_passagers_adultes2").value = adultCount;
    document.getElementById("nb_passagers_childs2").value = childCount;
    document.getElementById("nb_passagers_babies2").value = babyCount;

    // Mettre à jour le texte visible dans la modal "Devis"
    document.getElementById(
        "nbprsn2"
    ).textContent = `${adultCount} Adulte - ${childCount} Enfant - ${babyCount} Bébé (total : ${
        parseInt(adultCount) + parseInt(childCount) + parseInt(babyCount)
    })`;
}


$(document).ready(function () {
    $(document).click(function (event) {
        if (
            !$(event.target).closest("#nbpassagers, #passagersdropdown").length
        ) {
            if ($("#passagersdropdown").is(":visible")) {
                $("#passagersdropdown").hide();
                syncPassengerValues();
            }
        }
    });

    function updateHiddenPassengerFields() {
        $("#nb_passagers_adultes_hidden").val($adultnb);
        $("#nb_passagers_childs_hidden").val($enfantnb);
        $("#nb_passagers_babies_hidden").val($bebenb);
    }
    $adultnb = parseInt($("#nb_passagers_adultes").val());
    $enfantnb = parseInt($("#nb_passagers_childs").val());
    $bebenb = parseInt($("#nb_passagers_babies").val());
    $total = 1;

    $("#addadulat").click(function () {
        if ($total <= 8) {
            $adultnb += 1;
            $total += 1;
            $("#adult_m").text($adultnb);
            $("#nb_passagers_adultes").val($adultnb);
            $("#message_div").text("");
        } else {
            $("#message_div").text("maximum 9 passagers\n");
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );

        syncPassengerValues();
    });
    $("#removeadulat").click(function () {
        if ($adultnb > 1) {
            $adultnb -= 1;
            $total -= 1;
            $("#adult_m").text($adultnb);
            $("#nb_passagers_adultes").val($adultnb);
            $("#message_div").text("");
        } else {
            $("#message_div").text("minimum 9 passagers\n");
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );
    });
    /////add enfant
    $("#addenfant").click(function () {
        if ($total <= 8) {
            $enfantnb = $enfantnb + 1;
            $total += 1;
            $("#enfant_m").text($enfantnb);
            $("#nb_passagers_childs").val($enfantnb);
            $("#message_div").text("");
        } else {
            $("#message_div").text("maximum 9 passagers\n");
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );
    });
    /////remove enfant
    $("#removeenfant").click(function () {
        if ($enfantnb >= 1) {
            $enfantnb -= 1;
            $total -= 1;
            $("#enfant_m").text($enfantnb);
            $("#nb_passagers_childs").val($enfantnb);
            $("#message_div").text("");
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );
    });
    /////add bebe
    $("#addbebe").click(function () {
        if ($total <= 8) {
            $bebenb += 1;
            $total += 1;
            $("#bebe_m").text($bebenb);
            $("#nb_passagers_babies").val($bebenb);
            $("#message_div").text("");

            // --------vvvvvvvvvv---calll--vvvvvvvv-------------

            updateHiddenPassengerFields();
        } else {
            $("#message_div").text("maximum 9 passagers\n");
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );
    });
    /////remove bebe
    $("#removebebe").click(function () {
        if ($bebenb >= 1) {
            $bebenb -= 1;
            $total -= 1;
            $("#bebe_m").text($bebenb);
            $("#nb_passagers_babies").val($bebenb);
        }
        $("#nbprsn").text(
            $("#nb_passagers_adultes").val() +
                " Adulte - " +
                $("#nb_passagers_childs").val() +
                " Enfant -" +
                $("#nb_passagers_babies").val() +
                " Bébé total(" +
                $total +
                ")"
        );
    });
    $("#aller").change(function () {
        $("#Retour").attr("min", $("#aller").val());
    });
    $display = 0;
    $("#nbpassagers").click(function () {
        if ($display == 0) {
            $("#passagersdropdown").show();
            $display = 1;
        } else if ($display == 1) {
            $("#passagersdropdown").hide();
            $display = 0;
        }
    });
    $(".closepassagersmodal").click(function () {
        $("#passagersdropdown").hide();
        $display = 0;
        syncPassengerValues();
    });
});

// ---------------------------------------------^^^^^^-----dropdown1------^^^^^^^^^^^^^^--------------------------------------------- --}}

// -----------------------------------------------------vvvvvvv----------------------------------dropdown2---------------vvvvvvvvv---------- --}}

$(document).ready(function () {
    $(document).click(function (event) {
        if (
            !$(event.target).closest("#nbpassagers2, #passagersdropdown2")
                .length
        ) {
            $("#passagersdropdown2").hide();
        }
    });

    let adultNb2 = 0;
    let enfantNb2 = 0;
    let bebeNb2 = 0;
    let total2 = 0;

    function updatePassengerCountDisplay() {
        $("#nbprsn2").text(
            adultNb2 +
                " Adulte - " +
                enfantNb2 +
                " Enfant - " +
                bebeNb2 +
                " Bébé (total : " +
                total2 +
                ")"
        );
    }

    function updateHiddenPassengerFieldsForDevis() {
        $("#nb_passagers_adultes2").val(adultNb2);
        $("#nb_passagers_childs2").val(enfantNb2);
        $("#nb_passagers_babies2").val(bebeNb2);
    }

    function initializeDevisModal() {
        adultNb2 = parseInt($("#nb_passagers_adultes2").val());
        enfantNb2 = parseInt($("#nb_passagers_childs2").val());
        bebeNb2 = parseInt($("#nb_passagers_babies2").val());
        total2 = adultNb2 + enfantNb2 + bebeNb2;

        $("#adult_m2").text(adultNb2);
        $("#enfant_m2").text(enfantNb2);
        $("#bebe_m2").text(bebeNb2);

        updatePassengerCountDisplay();
    }

    // Ajouter un adulte
    $("#addadulat2").click(function () {
        if (total2 < 9) {
            adultNb2 += 1;
            total2 += 1;
            $("#adult_m2").text(adultNb2);
            updateHiddenPassengerFieldsForDevis();
        } else {
            $("#message_div2").text("Maximum 9 passagers\n");
        }
        updatePassengerCountDisplay();
    });

    // Retirer un adulte
    $("#removeadulat2").click(function () {
        if (adultNb2 > 1) {
            adultNb2 -= 1;
            total2 -= 1;
            $("#adult_m2").text(adultNb2);
            updateHiddenPassengerFieldsForDevis();
        } else {
            $("#message_div2").text("Minimum 1 adulte\n");
        }
        updatePassengerCountDisplay();
    });

    // Ajouter un enfant
    $("#addenfant2").click(function () {
        if (total2 < 9) {
            enfantNb2 += 1;
            total2 += 1;
            $("#enfant_m2").text(enfantNb2);
            updateHiddenPassengerFieldsForDevis();
        } else {
            $("#message_div2").text("Maximum 9 passagers\n");
        }
        updatePassengerCountDisplay();
    });

    // Retirer un enfant
    $("#removeenfant2").click(function () {
        if (enfantNb2 > 0) {
            enfantNb2 -= 1;
            total2 -= 1;
            $("#enfant_m2").text(enfantNb2);
            updateHiddenPassengerFieldsForDevis();
        }
        updatePassengerCountDisplay();
    });

    // Ajouter un bébé
    $("#addbebe2").click(function () {
        if (total2 < 9) {
            bebeNb2 += 1;
            total2 += 1;
            $("#bebe_m2").text(bebeNb2);
            updateHiddenPassengerFieldsForDevis();
        } else {
            $("#message_div2").text("Maximum 9 passagers\n");
        }
        updatePassengerCountDisplay();
    });

    // Retirer un bébé
    $("#removebebe2").click(function () {
        if (bebeNb2 > 0) {
            bebeNb2 -= 1;
            total2 -= 1;
            $("#bebe_m2").text(bebeNb2);
            updateHiddenPassengerFieldsForDevis();
        }
        updatePassengerCountDisplay();
    });
    // Ouvrir la modal
    $("#nbpassagers2").click(function () {
        $("#passagersdropdown2").show();
        initializeDevisModal();
    });
    // Fermer la modal
    $(".closepassagersmodal2").click(function () {
        $("#passagersdropdown2").hide();
    });
    initializeDevisModal();
});