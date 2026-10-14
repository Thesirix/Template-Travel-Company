let metricsReqId = 0;
let pendingMetricsXhr = null;
let lastAgentMetricsData = [];

function buildTableRows(metricsData) {
    const tbody = document.querySelector("#infos-table tbody");
    const agentMetricsMap = {};
    metricsData.forEach((row) => {
        agentMetricsMap[row.agent_id] = row;
    });

    let html = "";
    const allLanes = document.querySelectorAll(".fc-timeline-lane");
    allLanes.forEach((el) => {
        if (el.classList.contains("fc-resource-group")) {
            html += `<tr class="spacer-row"><td colspan="4" style="height:33px;"></td></tr>`;
        } else if (el.classList.contains("fc-resource")) {
            const rid = el.getAttribute("data-resource-id");
            const agentId =
                rid && rid.startsWith("gent-") ? rid.split("-")[1] : null;
            const metrics = agentMetricsMap[agentId];
            const laneHeight = el.offsetHeight;
            if (metrics) {
                html += `<tr style="height:${laneHeight}px">
                    <td>${metrics.solde}</td>
                    <td>${metrics.pris}</td>
                    <td>${metrics.cotiser}</td>
                    <td>${metrics.restant}</td>
                </tr>`;
            } else {
                html += `<tr style="height:${laneHeight}px"><td colspan="4">-</td></tr>`;
            }
        }
    });

    tbody.innerHTML = html;
}

function updateRightTable() {
    const tbody = document.querySelector("#infos-table tbody");
    const cs = calendar.view.currentStart;
    const yyyy = cs.getFullYear();
    const mm = String(cs.getMonth() + 1).padStart(2, "0");
    const firstDayOfMonth = `${yyyy}-${mm}-01`;

    if (lastAgentMetricsData.length > 0) buildTableRows(lastAgentMetricsData);

    if (window.planningBoot && window.planningBoot.start === firstDayOfMonth && window.takeBoot) {
        const boot = window.takeBoot("metrics");
        if (boot) {
            lastAgentMetricsData = boot;
            buildTableRows(boot);
            return;
        }
    }

    // Même mois déjà en cours de calcul : on attend sa réponse au lieu de relancer le calcul
    // (les callbacks du calendrier appellent cette fonction plusieurs fois d'affilée).
    if (pendingMetricsXhr && pendingMetricsXhr.readyState !== 4) {
        if (pendingMetricsXhr.tcStart === firstDayOfMonth) return;
        pendingMetricsXhr.abort();
    }

    const myReqId = ++metricsReqId;
    pendingMetricsXhr = $.get(window.route.planningMetrics, {
        start: firstDayOfMonth,
    })
        .done(function (metricsData) {
            if (myReqId !== metricsReqId) return;
            lastAgentMetricsData = metricsData;
            buildTableRows(metricsData);
        })
        .fail(function (xhr, status) {
            if (status !== "abort" && window.TC) TC.toast.error("Impossible de charger les soldes des agents.");
        });
    pendingMetricsXhr.tcStart = firstDayOfMonth;
}

const updateRightTableDebounced = debounce(updateRightTable, 150);
window.updateRightTable = updateRightTable;
window.updateRightTableDebounced = updateRightTableDebounced;

window.addEventListener("resize", () => {
    if (lastAgentMetricsData.length > 0) {
        buildTableRows(lastAgentMetricsData);
    }
});
