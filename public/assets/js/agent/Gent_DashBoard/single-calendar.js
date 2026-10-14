// ----------------- Emploie Du Temps -----------------

var calendarPersonalEl = document.getElementById("calendar-personal");
var calendarPersonal = new FullCalendar.Calendar(calendarPersonalEl, {
    themeSystem: "bootstrap",
    locale: "fr",
    initialView: "dayGridMonth",
    height: 500,
    contentHeight: "auto",
    selectable: true,
    editable: true,
    slotMinTime: "09:00:00",
    slotMaxTime: "21:00:00",
    events: route.emploiEvents,
    firstDay: 1,

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
        return {
            html:
                '<div style="text-align:center; width:100%; color:#fff;">' +
                arg.event.title +
                "</div>",
        };
    },

    eventDidMount: function (info) {
        if (info.event.backgroundColor) {
            info.el.style.backgroundColor = info.event.backgroundColor;
        }

        info.el.addEventListener("contextmenu", function (e) {
            e.preventDefault();

            const couleurs = [
                "#1067c4",
                "#007bff",
                "#28a745",
                "#ffc107",
                "#17a2b8",
                "#6f42c1",
                "#fd7e14",
                "#6610f2",
                "#e83e8c",
                "#20c997",
                "#d63384",
                "#198754",
                "#0dcaf0",
                "#ff6f61",
                "#6f42c1",
                "#ff9f00",
                "#795548",
                "#009688",
                "#3f51b5",
                "#9c27b0",
                "#00bcd4",
                "#ff5722",
                "#4caf50",
                "#cddc39",
            ];

            const menu = document.createElement("div");
            menu.classList.add("context-menu");

            menu.style.cssText = `
        position: absolute;
        top: ${e.pageY - 195}px;
        left: ${e.pageX + 20}px;
        background: #fff;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 12px;
        z-index: 10000;
        display: grid;
        grid-template-columns: repeat(6, 30px);
        gap: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    `;

            couleurs.forEach((color) => {
                const swatch = document.createElement("div");
                swatch.classList.add("color-swatch");
                swatch.style.backgroundColor = color;

                swatch.onclick = function () {
                    info.event.setProp("backgroundColor", color);
                    info.event.setExtendedProp("color", color);
                    info.el.style.backgroundColor = color;

                    $.ajax({
                        type: "POST",
                        url: route.emploiUpdateColor,
                        data: {
                            _token: route.csrfToken,
                            id: info.event.id,
                            color: color,
                        },
                        success: function (response) {
                            if (response.success) {
                                showMessage(
                                    "Couleur mise à jour avec succès !"
                                );
                            } else {
                                showMessage("Erreur : " + response.error);
                            }
                        },
                        error: function () {
                            showMessage("Erreur lors de la sauvegarde.");
                        },
                    });

                    menu.remove();
                };

                menu.appendChild(swatch);
            });

            document
                .querySelectorAll(".context-menu")
                .forEach((el) => el.remove());
            document.body.appendChild(menu);

            setTimeout(() => {
                document.addEventListener("click", function remover() {
                    menu.remove();
                    document.removeEventListener("click", remover);
                });
            }, 100);
        });
    },

    eventDrop: function (info) {
        let eventId = info.event.id;
        if (!eventId) {
            console.error("Erreur : Aucun ID d'événement trouvé !");
            showMessage("Erreur : Impossible de modifier l'événement.");
            info.revert();
            return;
        }
        let newStart = new Date(info.event.start);
        newStart.setMinutes(
            newStart.getMinutes() - newStart.getTimezoneOffset()
        );
        let formattedStart = newStart.toISOString().split("T")[0];

        let newEnd;
        if (info.event.end) {
            newEnd = new Date(info.event.end);
            newEnd.setMinutes(newEnd.getMinutes() - newEnd.getTimezoneOffset());
        } else {
            newEnd = new Date(info.event.start);
            newEnd.setDate(newEnd.getDate() + 1);
            newEnd.setMinutes(newEnd.getMinutes() - newEnd.getTimezoneOffset());
        }
        let formattedEnd = newEnd.toISOString().split("T")[0];

        let data = {
            id: eventId,
            start_date: formattedStart,
            end_date: formattedEnd,
            _token: route.csrfToken,
        };

        $.ajax({
            url: route.emploiUpdate,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.success) {
                    info.event.setDates(newStart, newEnd, {
                        allDay: true,
                    });
                    showMessage("Mise à jour réussie !");
                } else {
                    showMessage("Erreur : " + response.error);
                    info.revert();
                }
            },
            error: function (xhr) {
                console.error("Erreur AJAX :", xhr.responseText);
                showMessage("Erreur lors de la mise à jour.");
                info.revert();
            },
        });
    },
    eventClick: function (info) {
        $("#deleteeventmodal").modal("show");
        $("#deleteevent").attr("data-type", "emploi");
        $("#eventid").val(info.event.id);
    },
    select: function (info) {
        var title = prompt("Entrez le titre de l'événement");
        if (title) {
            $.ajax({
                type: "POST",
                url: route.emploiAdd,
                data: {
                    _token: route.csrfToken,
                    title: title,
                    start_date: info.startStr,
                    end_date: info.endStr,
                },
                success: function (data) {
                    const tmpId = "tmp_" + Date.now();
                    const tmpEvent = calendarPersonal.addEvent({
                        id: tmpId,
                        title: title,
                        start: info.startStr,
                        end: info.endStr,
                        color: data.color,
                    });

                    calendarPersonal.refetchEvents();
                    tmpEvent.remove();
                },
                error: function () {
                    console.error("Erreur lors de l'ajout de l'événement.");
                },
            });
        }
    },
});
