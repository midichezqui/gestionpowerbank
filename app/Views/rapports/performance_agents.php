<div class="p-4 sm:p-6">

    <h1 class="text-xl font-semibold text-slate-900 mb-4">
        <i class="fa-solid fa-chart-line text-sky-600"></i>
        Performance des agents
    </h1>

    <!-- Formulaire de filtre + exports -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow flex flex-col gap-4">
        <form class="flex flex-col sm:flex-row gap-4"
              method="get"
              action="index.php">

            <input type="hidden" name="controller" value="rapport">
            <input type="hidden" name="action" value="performanceAgents">

            <div>
                <label class="text-xs text-slate-600">Date début</label>
                <input type="date" name="start" value="<?= $start ?>"
                       class="px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="text-xs text-slate-600">Date fin</label>
                <input type="date" name="end" value="<?= $end ?>"
                       class="px-3 py-2 border rounded-lg">
            </div>

            <button class="bg-sky-600 text-white px-4 py-2 rounded-lg mt-4 sm:mt-auto">
                Filtrer
            </button>
        </form>

        <div class="flex flex-wrap gap-2 text-xs">
            <form method="get" action="index.php" class="inline-flex items-center gap-1">
                <input type="hidden" name="controller" value="rapport">
                <input type="hidden" name="action" value="performanceAgentsCsv">
                <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Export CSV</span>
                </button>
            </form>
            <form method="get" action="index.php" class="inline-flex items-center gap-1">
                <input type="hidden" name="controller" value="rapport">
                <input type="hidden" name="action" value="performanceAgentsPdf">
                <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Export PDF</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 overflow-x-auto">
        <table class="min-w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-4 py-2">Agent</th>
                    <th class="px-4 py-2 text-center">Locations</th>
                    <th class="px-4 py-2 text-right">Chiffre (FC)</th>
                    <th class="px-4 py-2 text-center">Pénalités dues</th>
                    <th class="px-4 py-2 text-center">Pénalités payées</th>
                    <th class="px-4 py-2 text-center">Indice mauvais agent</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                <?php foreach ($stats as $row): ?>
                    <?php
                    $totalChiffre   = (float)($row['totalChiffre'] ?? 0);
                    $totalPenalites = (int)($row['penalitesNonPayees'] ?? 0) + (int)($row['penalitesPayees'] ?? 0);
                    $ratio          = $totalChiffre > 0 ? ($totalPenalites / $totalChiffre) * 100 : 0;

                    // Classe CSS selon le ratio : >10% rouge, entre 5 et 10% orange, sinon neutre
                    $rowClass = 'hover:bg-slate-50';
                    if ($ratio > 10) {
                        $rowClass .= ' bg-red-50 text-red-800';
                    } elseif ($ratio > 5) {
                        $rowClass .= ' bg-amber-50 text-amber-800';
                    }
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="px-4 py-2"><?= htmlspecialchars($row['agentNom']) ?></td>

                        <td class="px-4 py-2 text-center font-semibold">
                            <?= $row['totalLocations'] ?>
                        </td>

                        <td class="px-4 py-2 text-right font-medium text-slate-800">
                            <?= number_format($totalChiffre, 2) ?>
                        </td>

                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-[11px]">
                                <?= (int)($row['penalitesNonPayees'] ?? 0) ?>
                            </span>
                        </td>

                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px]">
                                <?= (int)($row['penalitesPayees'] ?? 0) ?>
                            </span>
                        </td>

                        <td class="px-4 py-2 text-center text-xs">
                            <?= $totalChiffre > 0 ? number_format($ratio, 2) . ' %' : '0 %' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</div>
