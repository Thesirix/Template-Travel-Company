/**
 * Données intégrées à la page pour le mois affiché à l'ouverture. Chaque donnée ne sert qu'une fois,
 * et seulement si le calendrier affiche bien ce mois-là ; ensuite le serveur est interrogé normalement.
 */
function takeBoot(key, fetchInfo) {
    const boot = window.planningBoot;
    if (!boot || !(key in boot)) return null;
    if (fetchInfo && fetchInfo.startStr.slice(0, 10) !== boot.start) return null;
    const value = boot[key];
    delete boot[key];
    return value;
}
window.takeBoot = takeBoot;

document.addEventListener("DOMContentLoaded", function () {
    const calendarEl = document.getElementById("calendar");
    const infosTable = document.querySelector("#agentInfos table");
    let selectedEvent = null;

    function genererZonesInterdites(startYear, endYear) {
        const zones = [];
        for (let year = startYear; year <= endYear; year++) {
            zones.push({
                start: `${year}-04-01`,
                end: `${year}-08-01`,
                display: "background",
                classNames: ["danger-zone"],
            });
        }
        return zones;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        height: "auto",
        contentHeight: "auto",
        schedulerLicenseKey: "CC-Attribution-NonCommercial-NoDerivatives",
        initialView: "resourceTimelineMonth",
        locale: "fr",
        selectable: true,
        slotMinTime: "09:00:00",
        slotMaxTime: "21:00:00",
        slotMinWidth: 36,
        resourceAreaWidth: "170px",
        // Premier affichage : données intégrées à la page (window.planningBoot), puis appels au serveur
        resources: function (info, successCallback, failureCallback) {
            const boot = takeBoot("resources");
            if (boot) return successCallback(boot);
            fetch(window.route.planningResources).then((res) => res.json()).then(successCallback).catch(failureCallback);
        },

        eventSources: [
            {
                events: function (fetchInfo, successCallback, failureCallback) {
                    const boot = takeBoot("events", fetchInfo);
                    if (boot) return successCallback(boot);
                    const url = `${window.route.planningEvents}?start=${encodeURIComponent(fetchInfo.startStr)}&end=${encodeURIComponent(fetchInfo.endStr)}`;
                    fetch(url).then((res) => res.json()).then(successCallback).catch(failureCallback);
                },
            },
            // Présences (fond gris) depuis /planning/attendances
            {
                events: function (fetchInfo, successCallback, failureCallback) {
                    const start = fetchInfo.startStr.slice(0, 10);
                    const end = fetchInfo.endStr.slice(0, 10);
                    const url =
                        window.route.planningAttendances +
                        `?start=${start}&end=${end}`;

                    const boot = takeBoot("attendances", fetchInfo);
                    (boot ? Promise.resolve(boot) : fetch(url).then((res) => res.json()))
                        .then((map) => {
                            const evts = [];
                            for (const key in map) {
                                const dates = map[key] || [];

                                for (const dateStr of dates) {
                                    const end = new Date(dateStr);
                                    end.setDate(end.getDate() + 1);

                                    evts.push({
                                        start: dateStr,
                                        end: end.toISOString().slice(0, 10),
                                        display: "background",
                                        resourceId: key,
                                        backgroundColor: "#C0C0C0",
                                        classNames: ["presence-bg"],
                                    });
                                }
                            }
                            successCallback(evts);
                        })
                        .catch(failureCallback);
                },
            },
            // Danger zone (fond rouge clair)
            {
                events: function (fetchInfo, successCallback, failureCallback) {
                    const startYear = fetchInfo.start.getFullYear();
                    const endYear = fetchInfo.end.getFullYear();
                    const zones = genererZonesInterdites(startYear, endYear);
                    successCallback(zones);
                },
            },
        ],

        resourceGroupField: "groupId",
        resourceGroupLabelContent: () => "",

        resourceGroupLaneDidMount(arg) {
            const agence = (arg.groupValue || "").split("#")[0].toUpperCase();
            arg.el.innerHTML = `<div style="
        display:flex;
        justify-content:center;
        align-items:center;
        width:100%;height:100%;
        font-weight:bold;
        font-size:16px;
      ">${agence}</div>`;
            arg.el.style.backgroundColor = "#fff2cc";
            arg.el.style.height = "33px";
        },

        resourceAreaColumns: [
            {
                field: "title",
                headerContent: "Agents",
            },
        ],
        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
            meridiem: false,
        },
        displayEventTime: false,

        views: {
            resourceTimelineDay: {
                slotDuration: {
                    hours: 1,
                },

                slotLabelFormat: [
                    {
                        hour: "numeric",
                        hour12: false,
                    },
                ],
                slotLabelAlign: "center",
            },
            resourceTimelineWeek: {
                slotDuration: {
                    days: 1,
                },
                slotLabelFormat: [
                    {
                        weekday: "short",
                    },
                    {
                        day: "numeric",
                    },
                ],
                slotLabelAlign: "center",
                slotLabelVerticalAlign: "top",
            },
            resourceTimelineMonth: {
                slotDuration: {
                    days: 1,
                },
                slotLabelFormat: [
                    {
                        weekday: "short",
                    },
                    {
                        day: "numeric",
                    },
                ],
                slotLabelAlign: "center",
                slotLabelVerticalAlign: "top",
            },
        },

        resourcesSet() {
            requestAnimationFrame(() => {
                updateRightTableDebounced();
                alignRightTable();
            });
        },

        datesSet(info) {
            requestAnimationFrame(() => {
                updateRightTableDebounced();
                alignRightTable();
            });
        },

        eventsSet: function () {
            requestAnimationFrame(() => {
                updateRightTableDebounced();
                alignRightTable();
            });
        },

        headerToolbar: {
            left: "prev today next",
            center: "title",
            right: "resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth",
        },
        buttonText: {
            today: "Aujourd'hui",
            day: "Jour",
            week: "Semaine",
            month: "Mois",
        },

        eventContent: function (arg) {
            const status = arg.event.extendedProps.status;
            const type = arg.event.extendedProps.type;

            const textColor =
                status === "approved" && type === "conge" ? "#000" : "#fff";

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
                color: ${textColor};
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                ${arg.event.title}
            </div>
        `,
            };
        },
        eventDidMount(info) {
            const status = info.event.extendedProps.status;
            const type = info.event.extendedProps.type;

            info.el.style.backgroundColor = info.event.backgroundColor;

            const div = info.el.querySelector("div");
            if (div) {
                div.style.color =
                    status === "approved" && type === "conge" ? "#000" : "#fff";
            }

            info.el.setAttribute("data-event-id", info.event.id);
        },

        eventClick: function (info) {
            window.selectedEvent = info.event;

            const s = info.event.extendedProps.status;
            const t = info.event.extendedProps.type;
            if (s === "maladie" || t === "maladie") {
                return;
            }

            $("#approveAgentName").text(info.event.extendedProps.agent_name);
            $("#approveReasonConge").text(info.event.title);
            $("#approveModal").modal("show");
        },
        select: function (info) {
            const agent = calendar.getResourceById(info.resource.id);
            if (!agent || !info.resource.id.startsWith("gent-")) return;

            const titre = prompt("Titre du congé pour " + agent.title);
            if (!titre) return;

            const endDate = new Date(info.endStr);
            endDate.setDate(endDate.getDate() - 1);
            const endFixed = endDate.toISOString().split("T")[0];

            const totalJours = calculerJoursEntre(info.startStr, endFixed);

            const joursParType = {
                conge: totalJours,
                repos: 0,
                ferie: 0,
                autre: 0,
                maladie: 0,
            };

            const match = (agent.id || "").match(/^gent-(\d+)/);
            const gentId = match ? match[1] : null;
            if (!gentId) {
                alert(
                    "Impossible de déterminer l'ID de l'agent (" +
                        agent.id +
                        ")"
                );
                return;
            }

            $.ajax({
                url: window.route.planningAdd,
                method: "POST",
                data: {
                    _token: window.route.csrfToken,
                    resourceId: parseInt(gentId, 10),
                    start_date: info.startStr,
                    end_date: info.endStr,
                    title: titre,
                    type: "conge",
                    jours_par_type: JSON.stringify(joursParType),
                },
                success: function (response) {
                    if (response.success) {
                        showMessage("✅ Congé ajouté avec succès !");
                        calendar.refetchEvents();
                    } else {
                        const msg = response.message || "⛔ Erreur inconnue.";
                        showMessage("⛔ " + msg);
                    }
                },
                error: function (xhr) {
                    alert(
                        "Erreur lors de l'ajout du congé : " + xhr.responseText
                    );
                },
            });
        },
    });

    window.calendar = calendar;
    calendar.render();

    // -------------------------fonction tableau de droite -------------------

    document.getElementById("calendar").addEventListener("click", (e) => {
        if (
            e.target.closest(".fc-datagrid-expander") ||
            e.target.closest(".fc-resource-group")
        ) {
            setTimeout(() => {
                if (lastAgentMetricsData.length > 0) {
                    buildTableRows(lastAgentMetricsData);
                }
            }, 0);
        }
    });

    function alignRightTable() {
        if (!infosTable) return;
        const toolbarH =
            calendarEl.querySelector(".fc-header-toolbar")?.offsetHeight || 0;
        const daysHeaderH =
            calendarEl.querySelector(".fc-timeline-header")?.offsetHeight || 0;
        infosTable.style.marginTop = toolbarH + daysHeaderH + "px";
    }

    calendarEl.addEventListener("click", (e) => {
        if (
            e.target.closest(".fc-datagrid-expander") ||
            e.target.closest(".fc-resource-group")
        ) {
            requestAnimationFrame(() => {
                updateRightTableDebounced();
                alignRightTable();
            });
        }
    });

    window.addEventListener("resize", () => {
        requestAnimationFrame(alignRightTable);
    });
});
