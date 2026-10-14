const LIMITE_JOURS = 15;

// Sauvegarde de l'ancienne valeur pour chaque champ
let ancienneDateDebut = "";
let ancienneDateFin = "";

(function initFocusTracing() {
    const fromEl = document.getElementById("modalfrom");
    const toEl = document.getElementById("modalto");

    document.getElementById("modalfrom").addEventListener("focus", function () {
        ancienneDateDebut = this.value;
    });
    document.getElementById("modalto").addEventListener("focus", function () {
        ancienneDateFin = this.value;
    });
})();

// Message court affiché en toast (thème : public/js/tc/ui.js)
showMessage = function (msg) {
    if (window.TC && TC.toast) {
        TC.toast({ type: /erreur|impossible|échec/i.test(msg) ? "error" : "success", message: msg, duration: 2500 });
    }
};

function getTotalLeaveDays() {
    const types = ["conge", "repos", "ferie", "autre"];
    const total = types.reduce((sum, id) => {
        const el = document.getElementById(id);
        const val = parseInt(el && el.value ? el.value : 0, 10) || 0;
        return sum + val;
    }, 0);

    return total;
}

function calculerJoursEntre(start, end) {
    const debut = new Date(start);
    const fin = new Date(end);

    if (isNaN(debut) || isNaN(fin)) {
        return 0;
    }

    debut.setHours(12, 0, 0, 0);
    fin.setHours(12, 0, 0, 0);

    if (fin < debut) {
        return 0;
    }

    const joursFeries = ["01-05", "14-07", "15-08", "25-12"];

    let count = 0;
    const current = new Date(debut);

    while (current <= fin) {
        const day = current.getDay();
        const dd = String(current.getDate()).padStart(2, "0");
        const mm = String(current.getMonth() + 1).padStart(2, "0");
        const code = `${dd}-${mm}`;

        const isSunday = day === 0;
        const isFerie = joursFeries.includes(code);

        if (!isSunday && !isFerie) count++;

        current.setDate(current.getDate() + 1);
        current.setHours(12, 0, 0, 0);
    }

    return count;
}

function getSelectedDuration() {
    const start = document.getElementById("modalfrom").value;
    const end = document.getElementById("modalto").value;

    return calculerJoursEntre(start, end);
}

function updateDays(type, delta) {
    const input = document.getElementById(type);
    if (!input) {
        return;
    }

    let currentValue = parseInt(input.value, 10) || 0;
    const durationLimit = getSelectedDuration();
    const totalCurrent = getTotalLeaveDays();

    if (delta > 0 && totalCurrent >= durationLimit) {
        showMessage(
            "⛔ Impossible d’ajouter plus de jours que la durée sélectionnée."
        );

        return;
    }

    let newValue = currentValue + delta;
    if (newValue < 0) newValue = 0;

    const newTotal = totalCurrent + delta;

    if (newTotal > durationLimit) {
        showMessage(
            "⛔ Total dépassé : vous avez déjà réparti tous les jours."
        );

        return;
    }

    input.value = newValue;
}

function autoRemplirConge() {
    const start = document.getElementById("modalfrom").value;
    const end = document.getElementById("modalto").value;

    if (!start || !end) {
        return;
    }

    const joursCalcDirect = calculerJoursEntre(start, end);

    try {
        const js =
            typeof getSelectedDuration === "function"
                ? getSelectedDuration.toString()
                : "";
        const soupçonNaif =
            js &&
            !js.includes("calculerJoursEntre") &&
            js.includes("fin - debut");
    } catch (e) {}

    const jours = joursCalcDirect;

    if (jours <= 0) {
        return;
    }

    ["conge", "repos", "ferie", "autre"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = 0;
    });

    const congeEl = document.getElementById("conge");
    if (congeEl) {
        congeEl.value = jours;
    } else {
    }
}

function verifierLimiteJours(e) {
    const startInput = document.getElementById("modalfrom");
    const endInput = document.getElementById("modalto");
    const start = startInput.value;
    const end = endInput.value;

    const jours = calculerJoursEntre(start, end);

    if (jours > LIMITE_JOURS) {
        showMessage("⛔ La durée maximale est de " + LIMITE_JOURS + " jours.");
        if (e.target === startInput) {
            startInput.value = ancienneDateDebut || "";
        } else {
            endInput.value = ancienneDateFin || "";
        }

        autoRemplirConge();
    } else {
        ancienneDateDebut = startInput.value;
        ancienneDateFin = endInput.value;
    }
}

document.getElementById("modalfrom").addEventListener("change", function (ev) {
    verifierLimiteJours(ev);
    if (document.getElementById("modalto").value) {
        autoRemplirConge();
    }
});

document.getElementById("modalto").addEventListener("change", function (ev) {
    verifierLimiteJours(ev);
    if (document.getElementById("modalfrom").value) {
        autoRemplirConge();
    }
});
