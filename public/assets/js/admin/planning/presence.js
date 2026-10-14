window.attendanceDates = [];

// Compteur intégré à la page ; à défaut, présences depuis le 1er janvier (et non tout l'historique)
if (window.planningBoot && window.planningBoot.yearPresences !== undefined) {
    document.addEventListener("DOMContentLoaded", () => {
        const el = document.getElementById("presenceCounterYear");
        if (el) el.textContent = `${window.planningBoot.yearPresences}`;
    });
} else fetch(`${window.route.planningAttendances}?start=${new Date().getFullYear()}-01-01`)
    .then((res) => res.json())
    .then((map) => {
        if (!map) return;

        let totalAnnee = 0;

        // On compte toutes les présences de tous les agents depuis le 1er janvier
        const anneeCourante = new Date().getFullYear();
        const debutAnnee = new Date(`${anneeCourante}-01-01`);

        for (const gentId in map) {
            const dates = map[gentId] || [];
            dates.forEach((dateStr) => {
                const date = new Date(dateStr);
                if (date >= debutAnnee) totalAnnee++;
            });
        }

        const compteurAnneeEl = document.getElementById("presenceCounterYear");
        if (compteurAnneeEl) {
            compteurAnneeEl.textContent = `${totalAnnee}`;
        }
    });
