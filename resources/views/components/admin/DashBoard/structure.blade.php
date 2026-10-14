    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Planning de l'équipe</h1>
                <p class="text-gray-500 mb-0">Congés, repos et présences de chaque agent, avec les soldes à jour.</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px">
                <span class="tc-chip"><span class="tc-dot" style="background: #f59e0b"></span> En attente</span>
                <span class="tc-chip"><span class="tc-dot" style="background: #16a34a"></span> Validé</span>
                <span class="tc-chip"><span class="tc-dot" style="background: rgba(225,29,72,.35)"></span> Haute saison</span>
                <button type="button" class="btn btn-primary" id="btnOuvrirModalAdminConge"><i class="fa-solid fa-plus mr-1"></i> Poser un congé</button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body tc-planning">
                {{-- Calendrier des congés par agent --}}
                <div id="calendar"></div>

                {{-- Soldes de chaque agent, alignés ligne à ligne sur le calendrier (table_metrics.js) --}}
                <div id="agentInfos">
                    <table id="infos-table" class="text-center">
                        <thead>
                            <tr>
                                <th>Solde</th>
                                <th>Pris</th>
                                <th>Cotisé</th>
                                <th>Restant</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
