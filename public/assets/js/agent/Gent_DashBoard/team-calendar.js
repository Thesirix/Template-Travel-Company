let selectedAgenceId = "";

$(document).on("click", ".agence-choice", function (e) {
    e.preventDefault();

    selectedAgenceId = $(this).data("id") || "";
    const agenceName = $(this).text().trim();

    $("#dropdownAgences").text(agenceName + " ▼");

    if (window.calendarTeam) {
        window.calendarTeam.refetchEvents();
    }
});

function openEditModal(event) {
    $("#modaltitle").val(event.title);
    $("#modalfrom").val(event.startStr || event.start);
    const end = new Date(event.endStr || event.end);
    end.setDate(end.getDate() - 1);
    $("#modalto").val(end.toISOString().split("T")[0]);
    $("#form-error-msg").hide();
    $("#addeventmodal").modal("show");
    $("#addeventbutton").data("edit-id", event.id);
}

function appliquerPresences(viewType) {
    document.querySelectorAll("#calendar-team .jour-present").forEach((el) => {
        el.classList.remove("jour-present");
        const tag = el.querySelector(".presence-tag");
        if (tag) tag.remove();
    });

    if (!Array.isArray(window.attendanceDates)) return;

    window.attendanceDates.forEach((dateStr) => {
        const selector =
            viewType === "dayGridMonth"
                ? `#calendar-team .fc-daygrid-day[data-date='${dateStr}']`
                : `#calendar-team .fc-timegrid-col[data-date='${dateStr}']`;

        const cell = document.querySelector(selector);
        if (cell) {
            cell.classList.add("jour-present");
        }
    });
}

// -------------------afichage solde ----------------

function formatSolde(val) {
    const n = Number(String(val ?? 0).replace(",", "."));
    if (!Number.isFinite(n)) return "0";

    // Si entier sans décimales, sinon 2 décimales
    return Number.isInteger(n) ? String(n) : n.toFixed(2);
}

function loadAgentCounters() {
    $.ajax({
        url: route.agentCounters,
        method: "GET",
        success: function (res) {
            if (res.success) {
                const b = res.balances || {};

                $("#solde-conge").text(formatSolde(b.conge));
                $("#solde-repos").text(formatSolde(b.repos));
                $("#solde-ferie").text(formatSolde(b.ferie));
                $("#solde-maladie").text(formatSolde(b.maladie));
                $("#solde-autre").text(formatSolde(b.autre));

                window.leaveBalances = {
                    conge: Number(String(b.conge).replace(",", ".")) || 0,
                    repos: Number(String(b.repos).replace(",", ".")) || 0,
                    ferie: Number(String(b.ferie).replace(",", ".")) || 0,
                    maladie: Number(String(b.maladie).replace(",", ".")) || 0,
                    autre: Number(String(b.autre).replace(",", ".")) || 0,
                };
            }
        },
        error: function () {
            console.error("Impossible de charger les soldes.");
        },
    });
}

document.addEventListener("DOMContentLoaded", function () {
    loadAgentCounters(); // charge les soldes au démarrage
});

// --------------------------------

// balance counter dynamique

function getSelectedDurationAgentStrict() {
    const start = $("#modalfrom").val();
    const end = $("#modalto").val();
    return calculerJoursEntre(start, end); // réutilise la bonne version
}

// ----------------------------

document.addEventListener("DOMContentLoaded", function () {
    function isDangerZone(dateStr) {
        const d = new Date(dateStr);
        const year = d.getFullYear();
        const start = new Date(`${year}-04-01`);
        const end = new Date(`${year}-08-01`); // 01/08 exclus
        return d >= start && d < end;
    }

    function validateDangerZone(start, end, type) {
        if (type !== "conge") return false; // seulement pour les congés
        const startDate = new Date(start);
        const endDate = new Date(end);
        for (
            let d = new Date(startDate);
            d <= endDate;
            d.setDate(d.getDate() + 1)
        ) {
            if (isDangerZone(d.toISOString().split("T")[0])) {
                return true;
            }
        }
        return false;
    }

    // --------------------------------
    function resetModalAjoutEvenement() {
        $("#addeventbutton").removeData("edit-id");
        modeMaladieActif = false;

        $("#btnMaladieToggle")
            .removeClass("active btn-success")
            .addClass("btn-danger")
            .show();

        $("#blocMaladieDates").hide();
        $("#modalfrom").closest(".row").show();
        $("#modalto").closest(".row").show();
        $("#congeTypes").closest(".row").show();

        $("#modaltitle").val("");
        $("#modalfrom").val("");
        $("#modalto").val("");
        $("#maladie_from").val("");
        $("#maladie_to").val("");
        ["conge", "repos", "ferie", "autre"].forEach((id) =>
            $("#" + id).val(0)
        );
        $("#form-error-msg").hide().text("");
    }

    $("#btnOuvrirModalEvenement").on("click", function () {
        resetModalAjoutEvenement();
        $("#addeventmodal").modal("show");
    });

    function setDateLimits() {
        const today = new Date().toISOString().split("T")[0];
        $("#modalfrom").attr("min", today);
        $("#modalto").attr("min", today);
    }

    $("#addeventmodal").on("hidden.bs.modal", function () {
        resetModalAjoutEvenement();
    });

    // ---------------------------anti date anterieur ----------------------------
    let lastValidEndDate = "";

    document.getElementById("modalto").addEventListener("focus", function () {
        lastValidEndDate = this.value;
    });

    document.getElementById("modalto").addEventListener("change", function () {
        const start = document.getElementById("modalfrom").value;
        const end = this.value;
    });

    // ----------------- Calendrier Congés -----------------------------------------------------------------------------------------

    function genererZonesInterdites(startYear, endYear) {
        const zones = [];
        for (let year = startYear; year <= endYear; year++) {
            zones.push({
                start: `${year}-04-01`,
                end: `${year}-08-01`,
                display: "background",
                overlap: false,
            });
        }
        return zones;
    }

    var calendarTeamEl = document.getElementById("calendar-team");
    var calendarTeam = new FullCalendar.Calendar(calendarTeamEl, {
        themeSystem: "bootstrap",
        locale: "fr",
        initialView: "dayGridMonth",
        height: 500,
        contentHeight: "auto",
        selectable: false,
        slotMinTime: "09:00:00",
        slotMaxTime: "21:00:00",

        firstDay: 1,

        eventSources: [
            {
                url: route.calendrierEvents,
                method: "GET",
                extraParams: function () {
                    return {
                        agence_id: selectedAgenceId || "",
                    };
                },
            },
            {
                events: function (fetchInfo, successCallback, failureCallback) {
                    const startYear = new Date(fetchInfo.start).getFullYear();
                    const endYear = new Date(fetchInfo.end).getFullYear();
                    const zones = genererZonesInterdites(startYear, endYear);
                    successCallback(zones);
                },
            },
        ],

        headerToolbar: {
            left: "prev today next",
            center: "title",
            right: "timeGridDay,timeGridWeek,dayGridMonth",
        },
        buttonText: {
            today: "Aujourd'hui",
            day: "Jour",
            week: "Semaine",
            month: "Mois",
        },
        displayEventTime: false,
        eventContent: function (arg) {
            const status = arg.event.extendedProps.status;
            const type = arg.event.extendedProps.type;

            let color = "#fff";
            if (status === "approved" && type === "conge") {
                color = "#000";
            }

            return {
                html: `
            <div style="
                text-align: center;
                width: 100%;
                height: 100%;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                font-size: 0.75rem;
                color: ${color};
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                ${arg.event.title}
            </div>
        `,
            };
        },

        eventDidMount: function (info) {
            const status = info.event.extendedProps.status;
            const type = info.event.extendedProps.type;

            info.el.style.backgroundColor = info.event.backgroundColor;

            const contentDiv = info.el.querySelector("div");

            if (status === "approved") {
                contentDiv.style.color = "#000";
                info.event.setProp("editable", false);
            } else {
                contentDiv.style.color = "#fff";
            }
        },

        eventClick: function (info) {
            const status = info.event.extendedProps.status;
            const reason = info.event.extendedProps.rejection_reason;
            const agentIdConnecte = route.gentid;
            const gentIdEvent = info.event.extendedProps.gent_id;

            // Événement refusé

            if (status === "rejected") {
                if (gentIdEvent != agentIdConnecte) {
                    showMessage(
                        "Vous ne pouvez pas modifier ou supprimer l’événement d’un autre agent."
                    );
                    return;
                }

                $("#refusalReasonText").text(
                    reason || "Aucune raison de refus n’a été précisée."
                );
                $("#refusalReasonModal").modal("show");

                $("#deleteRefusedRequest")
                    .off("click")
                    .on("click", function () {
                        $("#eventid").val(info.event.id);
                        $("#deleteevent")
                            .attr("data-type", "calendrier")
                            .click();
                        $("#refusalReasonModal").modal("hide");
                    });

                $("#editRefusedRequest")
                    .off("click")
                    .on("click", function () {
                        // -------------------------------------repartision des jour-----------------------------

                        const joursParType =
                            info.event.extendedProps.jours_par_type;
                        if (joursParType) {
                            try {
                                const jours =
                                    typeof joursParType === "string"
                                        ? JSON.parse(joursParType)
                                        : joursParType;

                                $("#conge").val(jours.conge || 0);
                                $("#repos").val(jours.repos || 0);
                                $("#ferie").val(jours.ferie || 0);
                                $("#autre").val(jours.autre || 0);
                            } catch (e) {
                                console.error(
                                    "Erreur de parsing jours_par_type",
                                    e
                                );
                            }
                        } else {
                            $("#conge").val(0);
                            $("#repos").val(0);
                            $("#ferie").val(0);
                            $("#autre").val(0);
                        }

                        $("#refusalReasonModal").modal("hide");
                        $("#modaltitle").val(info.event.title);

                        const start = new Date(info.event.start);
                        start.setDate(start.getDate() + 1);
                        $("#modalfrom").val(start.toISOString().split("T")[0]);

                        const end = new Date(info.event.end);
                        $("#modalto").val(end.toISOString().split("T")[0]);

                        $("#addeventbutton").data("edit-id", info.event.id);

                        modeMaladieActif = false;
                        $("#btnMaladieToggle").hide();
                        $("#addeventmodal").modal("show");
                    });

                return;
            }

            if (status === "approved") {
                showMessage(
                    "Cet événement est validé, vous ne pouvez pas le supprimer."
                );
                return;
            }

            if (gentIdEvent != agentIdConnecte) {
                showMessage(
                    "Vous ne pouvez pas modifier ou supprimer l’événement d’un autre agent."
                );
                return;
            }

            $("#deleteeventmodal").modal("show");
            $("#deleteevent").attr("data-type", "calendrier");
            $("#eventid").val(info.event.id);
        },
        datesSet: function (info) {
            appliquerPresences(info.view.type);
        },

        select: function (info) {
            var title = prompt("Entrez le titre de l'événement");
            if (title) {
                $.ajax({
                    type: "POST",
                    url: calendrierAdd,
                    data: {
                        _token: route.csrfToken,
                        title: title,
                        start_date: info.startStr,
                        end_date: info.endStr,
                    },
                    success: function (data) {
                        calendarTeam.addEvent({
                            id: data.id,
                            title: $("#modaltitle").val(),
                            start: info.startStr,
                            end: info.endStr,
                        });
                    },
                    error: function () {
                        console.error("Erreur lors de l'ajout de l'événement.");
                    },
                });
            }
        },
    });

    window.calendarTeam = calendarTeam;

    // On rend les deux calendriers

    calendarPersonal.render();
    calendarTeam.render();
    appliquerPresences(calendarTeam.view.type);

    toggleAddButton("#calendar1"); // cacher le "+" au chargement si on est sur emploi du temps

    // Pour savoir sur quel calendrier on agit

    var currentCalendar = calendarPersonal;

    function toggleAddButton(tab) {
        const btn = $("#btnOuvrirModalEvenement");

        if (tab === "#calendar1") {
            btn.css("visibility", "hidden");
        } else {
            btn.css("visibility", "visible");
        }
    }

    // Quand on change d’onglet, on re-render le calendrier concerné

    $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
        var target = $(e.target).attr("href");
        toggleAddButton(target);

        if (target === "#calendar1") {
            currentCalendar = calendarPersonal;
            calendarPersonal.render();
        } else if (target === "#calendar2") {
            currentCalendar = calendarTeam;
            calendarTeam.render();
        }
    });
    // ---------------------- Compteur annuel de jours travaillés ----------------------
    fetch(window.route.attendances, {
        credentials: "same-origin",
    })
        .then((res) => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            return res.json();
        })
        .then((dates) => {
            if (!Array.isArray(dates)) return;

            const anneeCourante = new Date().getFullYear();
            const debutAnnee = new Date(`${anneeCourante}-01-01`);
            let totalAnnee = 0;

            dates.forEach((dateStr) => {
                const date = new Date(dateStr);
                if (date >= debutAnnee) totalAnnee++;
            });

            const compteur = document.getElementById("presenceCounterYear");
            if (compteur) {
                compteur.textContent = `${totalAnnee}`;
            }
        })
        .catch((err) =>
            console.error("Erreur lors du comptage des présences :", err)
        );

    // ------------------------------Suppression d'un événement----------------------------

    $("#deleteevent").on("click", function () {
        const eventId = $("#eventid").val();
        const type = $(this).attr("data-type");
        const deleteUrl =
            type === "emploi" ? route.emploiDelete : route.calendrierDelete;

        $.ajax({
            type: "POST",
            url: deleteUrl,
            data: {
                _token: route.csrfToken,
                id: eventId,
            },
            success: function (data) {
                if (data.success) {
                    if (type === "emploi") {
                        const event = calendarPersonal.getEventById(eventId);
                        if (event) event.remove();
                    } else if (type === "calendrier") {
                        const event = calendarTeam.getEventById(eventId);
                        if (event) event.remove();
                    }
                    showMessage("Événement supprimé !");
                    loadAgentCounters();
                } else {
                    showMessage(data.error);
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    let res = JSON.parse(xhr.responseText);
                    showMessage(res.error);
                } else if (xhr.status === 404) {
                    showMessage(
                        "⛔ Ce congé a déjà été supprimé ou n'existe plus."
                    );
                } else {
                    showMessage(
                        "⛔ Erreur lors de la suppression : " + xhr.status
                    );
                }
            },
        });

        $("#deleteeventmodal").modal("hide");
    });

    $("#modalfrom, #modalto, #maladie_from, #maladie_to").on(
        "focus",
        function () {
            this.showPicker && this.showPicker();
        }
    );

    // ------------------------Ajout d'un événement ---------------------------------------

    $("#addeventbutton").on("click", function () {
        const title = $("#modaltitle").val().trim();
        const start = $("#modalfrom").val();
        const end = $("#modalto").val();
        const eventId = $(this).data("edit-id");

        // -----------------------anti same date -----------------------------
        if (eventId) {
            const oldEv = currentCalendar.getEventById(eventId);
            if (oldEv && oldEv.extendedProps.status === "rejected") {
                // Dates anciennes de l'événement existant
                const oldStart = new Date(oldEv.start);
                const oldEnd = new Date(oldEv.end);

                const newStart = modeMaladieActif
                    ? new Date(maladie_from)
                    : new Date(start);
                const newEnd = modeMaladieActif
                    ? new Date(maladie_to)
                    : new Date(end);

                oldStart.setHours(0, 0, 0, 0);
                oldEnd.setHours(0, 0, 0, 0);
                newStart.setHours(0, 0, 0, 0);
                newEnd.setHours(0, 0, 0, 0);

                oldEnd.setDate(oldEnd.getDate() - 1);
                newEnd.setDate(newEnd.getDate());

                const sameStart = oldStart.getTime() === newStart.getTime();
                const sameEnd = oldEnd.getTime() === newEnd.getTime();

                if (sameStart && sameEnd) {
                    $("#form-error-msg")
                        .text(
                            "⛔ Impossible de remettre exactement les mêmes dates sur une demande refusée."
                        )
                        .show();
                    return;
                }
            }
        }

        // -------------------------------------------------------------------

        const maladie_from = $("#maladie_from").val();
        const maladie_to = $("#maladie_to").val();

        let errorMsg = "";

        if (!title) errorMsg += "⛔ Le titre est requis.\n";

        if (modeMaladieActif) {
            // Contrôle mode maladie
            if (!maladie_from)
                errorMsg +=
                    "⛔ La date de début du congé maladie est requise.\n";
            if (!maladie_to)
                errorMsg += "⛔ La date de fin du congé maladie est requise.\n";
            if (
                maladie_from &&
                maladie_to &&
                new Date(maladie_to) < new Date(maladie_from)
            ) {
                errorMsg +=
                    "⛔ La date de fin maladie ne peut pas être avant la date de début.\n";
            }
        } else {
            // Contrôle classique
            if (!start) errorMsg += "⛔ La date de début est requise.\n";
            if (!end) errorMsg += "⛔ La date de fin est requise.\n";
            if (start && end && new Date(end) < new Date(start)) {
                errorMsg +=
                    "⛔ La date de fin ne peut pas être avant la date de début.\n";
            }
        }

        if (errorMsg !== "") {
            $("#form-error-msg").text(errorMsg).show();
            return;
        }

        let postData;
        let endMaladieFormatted = null;
        let endFormatted = null;

        if (modeMaladieActif) {
            const endMaladie = new Date(maladie_to);
            endMaladie.setDate(endMaladie.getDate() + 1);
            endMaladieFormatted = endMaladie.toISOString().split("T")[0];

            postData = {
                _token: route.csrfToken,
                title: title,
                start_date: maladie_from,
                end_date: endMaladieFormatted,
                type: "maladie",
            };
        } else {
            const endInclusive = new Date(end);
            endInclusive.setDate(endInclusive.getDate() + 1);
            endFormatted = endInclusive.toISOString().split("T")[0];

            postData = {
                _token: route.csrfToken,
                title: title,
                start_date: start,
                end_date: endFormatted,
            };
        }

        let joursParType = {};
        let mainType = "conge";

        if (!modeMaladieActif) {
            joursParType = {};
            let totalSelected = 0;

            ["conge", "repos", "ferie", "autre"].forEach((t) => {
                const val = parseInt($("#" + t).val()) || 0;
                joursParType[t] = val;
                totalSelected += val;
            });

            const duree = getSelectedDurationAgentStrict();
            if (totalSelected !== duree) {
                $("#form-error-msg")
                    .text(
                        `⛔ La répartition doit couvrir exactement toute la durée.`
                    )
                    .show();
                return;
            }
            if (totalSelected === 0) {
                $("#form-error-msg")
                    .text("⛔ Veuillez sélectionner au moins un type de congé.")
                    .show();
                return;
            }

            //  Vérification des soldes
            for (const [type, nb] of Object.entries(joursParType)) {
                if (nb > 0) {
                    const soldeDispo = parseFloat(
                        window.leaveBalances?.[type] || 0
                    );
                    if (nb > soldeDispo) {
                        $("#form-error-msg")
                            .text(
                                `⛔ Solde insuffisant pour le type : ${type} (disponible ${soldeDispo}, demandé ${nb})`
                            )
                            .show();
                        return;
                    }
                }
            }

            const nbRepos = joursParType["repos"] ?? 0;

            if (totalSelected === 1 && nbRepos === 1) {
                mainType = "repos";
            } else {
                mainType = "conge";
            }

            postData.type = mainType;
            postData.jours_par_type = joursParType;
        }

        //  Blocage agents si zone interdite
        if (
            (joursParType["conge"] ?? 0) > 0 &&
            validateDangerZone(start, end, "conge")
        ) {
            $("#form-error-msg")
                .text(
                    "⛔ Impossible de poser un congé entre le 01/04 et le 31/07."
                )
                .show();
            return;
        }

        let ajaxUrl = eventId ? route.calendrierUpdate : route.calendrierAdd;
        if (eventId) {
            postData.id = eventId;
        }

        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: postData,
            success: function (data) {
                if (data.success === false) {
                    $("#form-error-msg")
                        .text("⛔ " + data.message)
                        .show();
                    return;
                }

                // Rafraîchir les événements depuis le backend
                currentCalendar.refetchEvents();
                loadAgentCounters();

                $("#addeventmodal").modal("hide");
                if (modeMaladieActif) {
                    $("#btnMaladieToggle").trigger("click");
                }
                modeMaladieActif = false;
            },

            error: function (xhr) {
                let msg = "⛔ Une erreur est survenue.";
                if (xhr.status === 403 || xhr.status === 404) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                }
                showMessage(msg);
            },
        });
    });
});

// -------------------------hide congé maladie ---------------------------

let modeMaladieActif = false;

$("#btnMaladieToggle").on("click", function () {
    modeMaladieActif = !modeMaladieActif;

    $("#form-error-msg").hide().text("");

    if (modeMaladieActif) {
        $("#maladie_from").val("");
        $("#maladie_to").val("");
        $("#blocMaladieDates").show();
        $("#modalfrom").closest(".row").hide();
        $("#modalto").closest(".row").hide();
        $("#congeTypes").closest(".row").hide();
        $(this).addClass("active btn-success").removeClass("btn-danger");
    } else {
        $("#modalfrom").val("");
        $("#modalto").val("");
        $("#blocMaladieDates").hide();
        $("#modalfrom").closest(".row").show();
        $("#modalto").closest(".row").show();
        $("#congeTypes").closest(".row").show();
        $(this).removeClass("active btn-success").addClass("btn-danger");
    }
});
