function calculerJoursEntre(start, end) {
    const debut = new Date(start);
    const fin = new Date(end);

    // Neutralise le fuseau horaire (met les heures à midi)
    debut.setHours(12, 0, 0, 0);
    fin.setHours(12, 0, 0, 0);

    if (isNaN(debut) || isNaN(fin)) return 0;

    let count = 0;
    const joursFeries = [
        "01-05", // 1er mai
        "14-07", // 14 juillet
        "15-08", // 15 août
        "25-12", // 25 décembre
    ];

    const current = new Date(debut);
    while (current <= fin) {
        const day = current.getDay();
        const dd = String(current.getDate()).padStart(2, "0");
        const mm = String(current.getMonth() + 1).padStart(2, "0");
        const code = `${dd}-${mm}`;

        // On compte tous les jours sauf dimanche et fériés
        if (day !== 0 && !joursFeries.includes(code)) {
            count++;
        }

        current.setDate(current.getDate() + 1);
        current.setHours(12, 0, 0, 0);
    }

    return count;
}

function showMessage(msg) {
    var msgDiv = document.getElementById("statusMessage");
    msgDiv.innerText = msg;
    msgDiv.style.display = "block";
    msgDiv.style.zIndex = "9999";
    setTimeout(function () {
        msgDiv.style.display = "none";
    }, 2000);
}

const debounce = (fn, ms = 150) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
    };
};

function totalJoursAdmin() {
    let sum = 0;
    ["conge", "repos", "ferie", "autre"].forEach((type) => {
        sum += parseInt(
            document.getElementById("admin_" + type).value || 0,
            10
        );
    });
    return sum;
}

function totalJoursAdminEdit() {
    let sum = 0;
    ["conge", "repos", "ferie", "autre"].forEach((type) => {
        sum += parseInt(
            document.getElementById("admin_" + type + "_edit").value || 0,
            10
        );
    });
    return sum;
}

// --------------------- INCRÉMENTATION ---------------------

function updateDaysAdminEdit(type, delta) {
    const input = document.getElementById("admin_" + type + "_edit");
    let val = parseInt(input.value, 10) || 0;

    // Total actuel pour tous les types
    const total = totalJoursAdminEdit();
    const max = calculerJoursEntre(
        document.getElementById("adminStartDate").value,
        document.getElementById("adminEndDate").value
    );

    if (delta > 0 && total >= max) {
        showMessage(
            "⛔ Impossible d’ajouter plus de jours que la durée sélectionnée."
        );
        return;
    }

    if (delta < 0 && val <= 0) {
        return;
    }

    if (delta > 0 && total + delta > max) {
        showMessage(
            "⛔ Total dépassé : vous avez déjà réparti tous les jours."
        );
        return;
    }

    input.value = val + delta;
}

function updateDaysAdmin(type, delta) {
    const input = document.getElementById("admin_" + type);
    let val = parseInt(input.value, 10) || 0;
    const total = totalJoursAdmin();
    const max = calculerJoursEntre(
        document.getElementById("startDateConge").value,
        document.getElementById("endDateConge").value
    );

    if (delta > 0 && total >= max) {
        showMessage(
            "⛔ Impossible d’ajouter plus de jours que la durée sélectionnée."
        );
        return;
    }

    if (delta < 0 && val <= 0) {
        return;
    }

    if (delta > 0 && total + delta > max) {
        showMessage(
            "⛔ Total dépassé : vous avez déjà réparti tous les jours."
        );
        return;
    }

    input.value = val + delta;
}

// --------------------- AUTO-REMPLISSAGE ---------------------
function getSelectedDurationAdmin() {
    const start = document.getElementById("startDateConge").value;
    const end = document.getElementById("endDateConge").value;
    if (!start || !end) return 0;

    return calculerJoursEntre(start, end);
}

function autoRemplirCongeAdminEdit() {
    if (modeMaladieAdminEditActif) return;

    const start = document.getElementById("adminStartDate").value;
    const end = document.getElementById("adminEndDate").value;
    console.log("DEBUG start/end :", start, end);
    if (!start || !end) return;

    const jours = calculerJoursEntre(start, end);
    if (jours <= 0) return;

    // Réinitialise tous les champs
    [
        "admin_conge_edit",
        "admin_repos_edit",
        "admin_ferie_edit",
        "admin_autre_edit",
    ].forEach((id) => {
        const input = document.getElementById(id);
        if (input) input.value = 0;
    });

    // Remplit le type "congé" par défaut
    const congeInput = document.getElementById("admin_conge_edit");
    if (congeInput) congeInput.value = jours;
}

function autoRemplirCongeAdmin() {
    const jours = getSelectedDurationAdmin();
    if (jours <= 0) return;

    ["admin_conge", "admin_repos", "admin_ferie", "admin_autre"].forEach(
        (id) => {
            const input = document.getElementById(id);
            if (input) input.value = 0;
        }
    );

    const congeInput = document.getElementById("admin_conge");
    if (congeInput) congeInput.value = jours;
}
