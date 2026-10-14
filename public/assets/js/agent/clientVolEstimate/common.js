// Message d'erreur renvoyé par le serveur (validation, doublon...), sinon le texte par défaut
function serverError(xhr, fallback) {
    const json = xhr && xhr.responseJSON;
    if (json && json.errors) {
        return Object.values(json.errors).flat().join(' ');
    }
    return (json && json.message) || fallback;
}

$(document).ready(function () {
    if ("scrollRestoration" in history) {
        history.scrollRestoration = "manual";
    }
    if (sessionStorage.getItem("reloadPage") === "true") {
        setTimeout(function () {
            window.scrollTo(0, 0);
        }, 200); // Délai légèrement plus long
        sessionStorage.removeItem("reloadPage");
    }

    //boutons de réinitialisation
    $(".reset-button").click(function () {
        sessionStorage.setItem("reloadPage", "true");
        location.reload();
    });

    // Acordéon
    $(".accordion-header").on("click", function () {
        $(this).next(".accordion-content").slideToggle();
        $(this).toggleClass("active");
    });
});


$(document).ready(function () {
    // Initialiser Select2

    $(
        "#destination, #depart, #city_depart_devis, #arrival_city, #arrival_city_devis, #Compagnie"
    ).select2({
        theme: "form-control",
        placeholder: "Sélectionnez une option",
        allowClear: false,
        minimumResultsForSearch: 0,
        dropdownAutoWidth: true,
        closeOnSelect: true,
    });

    $("#Compagnie").on("select2:open", function () {
        $(".select2-results__options").css({
            "max-height": "300px", // ou 200px si tu veux moins
            "overflow-y": "auto"
        });
    });



    $("#departure_city").on("change", function () {
        const selectedValue = $(this).val();
        $("#city_depart_devis").val(selectedValue).trigger("change"); // Met à jour et déclenche l'événement Select2
    });

    $("#city_depart_devis").on("change", function () {
        const selectedValue = $(this).val();
        $("#departure_city").val(selectedValue).trigger("change");
    });

    // Font couleur gris clair
    $(
        "#destination, #depart, #city_depart_devis, #arrival_city, #arrival_city_devis, #Compagnie, #formule, #demande_bagage, #bagage-devis,#Véhicule,#Installation"
    ).each(function () {
        let $select = $(this);
        $select
            .next(".select2-container")
            .find(".select2-selection__rendered")
            .css("color", "#6c757d");
    });

    // Forcer le focus sur la barre de recherche au clic
    $(
        "#destination, #depart, #city_depart_devis, #arrival_city, #arrival_city_devis, #Compagnie"
    ).on("select2:open", function () {
        document.querySelector(".select2-search__field").focus();
    });

    // Fermer au clic à l'extérieur
    $(document).on("click", function (e) {
        if (!$(e.target).closest(".select2-container").length) {
            $(
                "#destination, #depart, #city_depart_devis, #arrival_city, #arrival_city_devis, #Compagnie"
            ).select2("close");
        }
    });
});

// -- --------------------------------------------------------------data load----------------------------------- --
// Référentiel mondial (aéroports, ports, compagnies) : chargé une fois, mis en cache par le navigateur.
// Format compact : aéroports [ville, pays, IATA], ports [nom, code pays], compagnies [nom, code].

$(document).ready(function () {
    $.getJSON(route.referenceData, initLocations);
});

function initLocations({airports, ports, airlines, maritimes}) {
    const formuleSelect = document.getElementById("formule");
    const destinationSelect = document.getElementById("destination");
    const arrivalCityFields = document.querySelectorAll(".arrival_city");
    const installationSelect = document.getElementById("Installation");
    const vehiculeSelect = document.getElementById("Véhicule");
    const compagnieSelect = document.getElementById("Compagnie");

    const portsByCountryCode = {};
    const airportsByCountryName = {};


    function sortByCounts(list) {
        // On récupère l'objet counts
        const counts = JSON.parse(localStorage.getItem('popular_airlines_counts') || '{}');
      
        return list.sort((a, b) => {
          const ca = counts[a[3]] || 0;
          const cb = counts[b[3]] || 0;
      
        // popularité décroissant
          if (cb !== ca) return cb - ca;
          // puis ordre alphabétique
          return a[1].localeCompare(b[1], 'fr');
        });
      }

    ports.forEach(([name, code]) => {
        (portsByCountryCode[code] ??= []).push(name);
    });

    airports.forEach(([city, country, iata]) => {
        (airportsByCountryName[country] ??= []).push([city, iata]);
    });

    // Villes de départ : favoris de l'agence puis tous les aéroports
    $("#depart, #city_depart_devis").each(function () {
        const select = $(this);
        select.append(new Option("─── Autres ───", "", false, false)).find("option:last").prop("disabled", true);
        airports.forEach(([city, , iata]) => select.append(new Option(`${city} (${iata})`, city)));
    });

    //synchroniser les dates entre Demande Client et Devis
    function syncDatesBetweenDemandAndDevis() {
        const departureDate = $("#departure_date").val();
        const returnDate = $("#return_date").val();
        $("#devis_departure_date").val(departureDate);
        $("#devis_return_date").val(returnDate);
    }

    $("#departure_date, #return_date").on(
        "change",
        syncDatesBetweenDemandAndDevis
    );
    syncDatesBetweenDemandAndDevis();

    // Synchronisation des champs de départ
    function syncDepartFields() {
        const departureCity = $("#depart").val();
        $("#city_depart_devis").val(departureCity).trigger("change.select2");
    }

    $("#depart").on("change", syncDepartFields);
    $("#city_depart_devis").on("change", function () {
        $("#depart").val($(this).val()).trigger("change.select2");
    });
    syncDepartFields();

    // Synchronisation des champs d'arrivée
    function syncArrivalFields() {
        const arrivalCity = $("#arrival_city").val();
        $("#arrival_city_devis").val(arrivalCity).trigger("change.select2");
    }

    $("#arrival_city").on("change", syncArrivalFields);
    $("#arrival_city_devis").on("change", function () {
        $("#arrival_city").val($(this).val()).trigger("change.select2");
    });
    syncArrivalFields();

    // Synchronisation des bagages
    function syncBagageFields() {
        const bagageValue = $("#demande_bagage").val();
        $("#bagage-devis").val(bagageValue).trigger("change.select2");
    }

    $("#demande_bagage").on("change", syncBagageFields);
    $("#bagage-devis").on("change", function () {
        $("#demande_bagage").val($(this).val()).trigger("change.select2");
    });
    syncBagageFields();

    // Gestion de la visibilité du champ bagage
    function updateBagageVisibility() {
        const selectedFormule = $("#formule").val();
        if (selectedFormule === "Maritime") {
            $("#bagage-row").hide();
            $("#bagage-row-devis").hide();
            $("#demande_bagage").val("Avec").trigger("change.select2");
            $("#bagage-devis").val("Avec").trigger("change.select2");
        } else {
            $("#bagage-row").show();
            $("#bagage-row-devis").show();
        }
    }

    $("#formule").on("change", updateBagageVisibility);
    updateBagageVisibility();

    function populateSelect(select, options) {
        $(select).empty();
        options.forEach((option) => {
            $(select).append(new Option(option.text, option.value));
        });
        $(select).trigger("change.select2");
    }

    // Filtrer les lieux d'arrivée en fonction de la formule et du pays sélectionné
    function filterLocationsForField(arrivalCitySelect) {
        const selectedFormule = formuleSelect.value;
        const [selectedCountryName, selectedCountryCode] =
            destinationSelect.value.split(",");

        let options = [];
        if (
            selectedFormule === "Maritime" &&
            portsByCountryCode[selectedCountryCode]
        ) {
            options = portsByCountryCode[selectedCountryCode].map((name) => ({
                value: name,
                text: name,
            }));
        } else if (
            selectedFormule === "Aérien" &&
            airportsByCountryName[selectedCountryName]
        ) {
            options = airportsByCountryName[selectedCountryName].map(
                ([city, iata]) => ({
                    value: city,
                    text: `${city} (${iata})`,
                })
            );
        }
        populateSelect(arrivalCitySelect, options);
    }

    function filterLocations() {
        arrivalCityFields.forEach(filterLocationsForField);
        const [selectedCountryName] = destinationSelect.value.split(",");
        const selectedFormule = formuleSelect.value;
        
      
        if (selectedFormule === "Maritime") {
            const maritimeOptions = [...maritimes]
                .sort((a, b) => a[0].localeCompare(b[0]))
                .map(([name, code]) => ({ value: code, text: name }));
            populateSelect(compagnieSelect, maritimeOptions);
        } else {
            const options = sortByCounts([...airlines]).map(([name]) => ({ value: name, text: name }));
            populateSelect(compagnieSelect, options);
        }
    
        updateInstallationOptions(selectedFormule);
        toggleInstallationAndVehiculeSections(selectedFormule);
        preFillArrivalCity();
    }


    $("#Compagnie").on("select2:select", function () {
        const sel = $(this).val();
        if (!sel) return;
        let counts = JSON.parse(localStorage.getItem('popular_airlines_counts') || '{}');
        counts[sel] = (counts[sel] || 0) + 1;
        localStorage.setItem('popular_airlines_counts', JSON.stringify(counts));  

      });

    function updateInstallationOptions(selectedFormule) {
        let options = [];
        if (selectedFormule === "Aérien") {
            options = [
                {
                    value: "Fauteuil",
                    text: "Fauteuil",
                },
            ];
        } else if (selectedFormule === "Maritime") {
            options = [
                {
                    value: "Cabine",
                    text: "Cabine",
                },
                {
                    value: "Couchette",
                    text: "Couchette",
                },
            ];
        }
        populateSelect(installationSelect, options);
    }

    // Cacher ou montrer les sections Installation et Véhicule
    function toggleInstallationAndVehiculeSections(selectedFormule) {
        if (selectedFormule === "Aérien") {
            $(installationSelect.closest(".row")).hide();
            $(vehiculeSelect.closest(".row")).hide();
            $(installationSelect).val("Fauteuil").trigger("change.select2");
            $(vehiculeSelect).val("Sans").trigger("change.select2");
        } else {
            $(installationSelect.closest(".row")).show();
            $(vehiculeSelect.closest(".row")).show();
        }
    }

    // Pré-remplir arrivée de Devis avec Demande Client
    function preFillArrivalCity() {
        const demandeClientArrivalCity = document.querySelector(
            ".arrival_city.demande-client"
        );
        if (demandeClientArrivalCity) {
            const arrivalCityValue = demandeClientArrivalCity.value;
            arrivalCityFields.forEach((arrivalCityField) => {
                if (!arrivalCityField.classList.contains("demande-client")) {
                    $(arrivalCityField)
                        .val(arrivalCityValue)
                        .trigger("change.select2");
                }
            });
        }
    }

    // Initialisation : premier pays de la liste
    function initializeDefault() {
        filterLocations();
        const defaultDestinationText = $(destinationSelect)
            .find(":selected")
            .text();
        $("#hidden_destination").val(defaultDestinationText);
    }

    $(destinationSelect).on("change", filterLocations);
    $(formuleSelect).on("change", filterLocations);
    $(".arrival_city.demande-client").on("change", preFillArrivalCity);

    initializeDefault();
}

// -- -------------------------------valeur bagage----------------------- --

document.addEventListener("DOMContentLoaded", function () {
    const formuleSelect = document.getElementById("formule");
    const bagageRow = document.getElementById("bagage-row");
    const bagageSelect = document.getElementById("demande_bagage");

    const bagageRowDevis = document.getElementById("bagage-row-devis");
    const bagageSelectDevis = document.getElementById("bagage-devis");

    updateBagageVisibility(formuleSelect.value);

    formuleSelect.addEventListener("change", function () {
        updateBagageVisibility(this.value);
    });

    function updateBagageVisibility(formule) {
        if (formule === "Maritime") {
            // Masquer le champ bagage et définir sa valeur par défaut sur "Avec"
            bagageRow.style.display = "none";
            bagageRowDevis.style.display = "none";
            bagageSelect.value = "Avec";
            bagageSelectDevis.value = "Avec";
        } else {
            // Afficher le champ bagage
            bagageRow.style.display = "flex";
            bagageRowDevis.style.display = "flex";
        }
    }
});