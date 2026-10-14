document.addEventListener("DOMContentLoaded", function () {
    // Champs pour Demande Client
    const departureInputDemand = document.querySelector("#departure_date");
    const returnInputDemand = document.querySelector("#return_date");
    // Champs pour Devis
    const departureInputDevis = document.querySelector("#devis_departure_date");
    const returnInputDevis = document.querySelector("#devis_return_date");
    // Configuration commune pour Flatpickr
    const flatpickrConfig = {
        dateFormat: "Y-m-d",
        altFormat: "d/m/Y", // Format to display to the user
        altInput: true, // Enables a second input for user-friendly display
        allowInput: true,
        minDate: new Date(),

        onClose: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 0 && dateStr) {
                const parts = dateStr.split("/");
                if (parts.length === 3) {
                    const d = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10) - 1;
                    const y = parseInt(parts[2], 10);
                    const testDate = new Date(y, m, d);
                    if (!isNaN(testDate.getTime()) && testDate >= new Date()) {
                        instance.setDate(testDate, true);
                    }
                }
            }
        },
    };

    // Initialiser Flatpickr pour Demande Client
    const returnPickerDemand = flatpickr(returnInputDemand, {
        ...flatpickrConfig,
    });

    const departurePickerDemand = flatpickr(departureInputDemand, {
        ...flatpickrConfig,
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                const departDate = selectedDates[0];
                returnPickerDemand.set("minDate", departDate);
                if (
                    returnPickerDemand.selectedDates.length > 0 &&
                    returnPickerDemand.selectedDates[0] < departDate
                ) {
                    returnPickerDemand.clear();
                }
                updateDevisMinDate(departDate);
            }
        },
    });

    // Initialiser Flatpickr pour Devis
    const returnPickerDevis = flatpickr(returnInputDevis, {
        ...flatpickrConfig,
    });

    const departurePickerDevis = flatpickr(departureInputDevis, {
        ...flatpickrConfig,
        onChange: function (selectedDates) {
            if (selectedDates.length > 0) {
                const departDate = selectedDates[0];
                returnPickerDevis.set("minDate", departDate);
                if (
                    returnPickerDevis.selectedDates.length > 0 &&
                    returnPickerDevis.selectedDates[0] < departDate
                ) {
                    returnPickerDevis.clear();
                }
            }
        },
    });

    // Mettre à jour la date minimale pour les champs Devis
    function updateDevisMinDate(minDate) {
        departurePickerDevis.set("minDate", minDate);
        returnPickerDevis.set("minDate", minDate);
    }

    // Synchroniser les dates entre Demande Client et Devis
    function syncDates() {
        const departureDate = departureInputDemand.value;
        const returnDate = returnInputDemand.value;
        departurePickerDevis.setDate(departureDate);
        returnPickerDevis.setDate(returnDate);
    }

    if (departureInputDemand && returnInputDemand) {
        departureInputDemand.addEventListener("change", syncDates);
        returnInputDemand.addEventListener("change", syncDates);
    }

    if (departurePickerDemand.selectedDates.length > 0) {
        updateDevisMinDate(departurePickerDemand.selectedDates[0]);
    }

    // Ouverture du calendrier lors du clic sur l'icône
    $(".input-date-icon").click(function () {
        const target = $(this).attr("target");
        console.log(target);
        document.querySelector(target)._flatpickr.toggle();
    });
});