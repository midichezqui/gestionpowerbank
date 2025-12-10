<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<div class="p-4 sm:p-6">

    <h1 class="text-xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-calendar-day text-sky-600"></i>
        Rapport des locations par date
    </h1>

    <!-- Formulaire de filtre -->
    <form method="get" class="bg-white shadow-sm border rounded-xl p-4 mb-6">
        <input type="hidden" name="controller" value="rapport">
        <input type="hidden" name="action" value="rapportParDate">

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">

            <div>
                <label class="text-xs text-slate-600 font-medium">Date début</label>
                <input type="date" name="date1" required
                       value="<?= htmlspecialchars($date1 ?? '') ?>"
                       class="w-full px-3 py-2 rounded-lg border focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Date fin</label>
                <input type="date" name="date2" required
                       value="<?= htmlspecialchars($date2 ?? '') ?>"
                       class="w-full px-3 py-2 rounded-lg border focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Agent</label>
                <select name="idAgent"
                        class="w-full px-3 py-2 rounded-lg border text-sm">
                    <option value="">Tous</option>
                    <?php foreach ($agents as $a): ?>
                        <option value="<?= (int)$a['id'] ?>"
                            <?= (!empty($idAgent) && (int)$idAgent === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nomComplet']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Quartier</label>
                <select name="idQuartier"
                        class="w-full px-3 py-2 rounded-lg border text-sm">
                    <option value="">Tous</option>
                    <?php foreach ($quartiers as $q): ?>
                        <option value="<?= (int)$q['id'] ?>"
                            <?= (!empty($idQuartier) && (int)$idQuartier === (int)$q['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['nomQuartier']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Pénalité</label>
                <select name="filtrePenalite"
                        class="w-full px-3 py-2 rounded-lg border text-sm">
                    <option value="" <?= empty($filtrePenalite) ? 'selected' : '' ?>>Toutes</option>
                    <option value="avec"     <?= $filtrePenalite === 'avec' ? 'selected' : '' ?>>Avec pénalité</option>
                    <option value="sans"     <?= $filtrePenalite === 'sans' ? 'selected' : '' ?>>Sans pénalité</option>
                    <option value="paye"     <?= $filtrePenalite === 'paye' ? 'selected' : '' ?>>Pénalité payée</option>
                    <option value="non_paye" <?= $filtrePenalite === 'non_paye' ? 'selected' : '' ?>>Pénalité non payée</option>
                </select>
            </div>

        </div>

        <div class="mt-4 flex flex-wrap gap-2 items-center">
            <button class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm">
                Rechercher
            </button>

            <?php if (!empty($data)): ?>
                <div class="flex flex-wrap gap-2 text-xs ml-1">
                    <!-- Export CSV -->
                    <form method="get" action="index.php" class="inline-flex items-center gap-1">
                        <input type="hidden" name="controller" value="rapport">
                        <input type="hidden" name="action" value="rapportParDateExcel">
                        <input type="hidden" name="date1" value="<?= htmlspecialchars($date1) ?>">
                        <input type="hidden" name="date2" value="<?= htmlspecialchars($date2) ?>">
                        <input type="hidden" name="idAgent" value="<?= htmlspecialchars($idAgent ?? '') ?>">
                        <input type="hidden" name="idQuartier" value="<?= htmlspecialchars($idQuartier ?? '') ?>">
                        <input type="hidden" name="filtrePenalite" value="<?= htmlspecialchars($filtrePenalite ?? '') ?>">
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-emerald-500 text-emerald-600 hover:bg-emerald-50">
                            <i class="fa-solid fa-file-csv"></i>
                            <span>Export CSV</span>
                        </button>
                    </form>

                    <!-- Export PDF -->
                    <form method="get" action="index.php" class="inline-flex items-center gap-1">
                        <input type="hidden" name="controller" value="rapport">
                        <input type="hidden" name="action" value="rapportParDatePdf">
                        <input type="hidden" name="date1" value="<?= htmlspecialchars($date1) ?>">
                        <input type="hidden" name="date2" value="<?= htmlspecialchars($date2) ?>">
                        <input type="hidden" name="idAgent" value="<?= htmlspecialchars($idAgent ?? '') ?>">
                        <input type="hidden" name="idQuartier" value="<?= htmlspecialchars($idQuartier ?? '') ?>">
                        <input type="hidden" name="filtrePenalite" value="<?= htmlspecialchars($filtrePenalite ?? '') ?>">
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-500 text-red-600 hover:bg-red-50">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Export PDF</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <?= implode("<br>", $errors) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($data)): ?>

        <?php
        // Totaux
        $totalCA       = 0.0;
        $totalPenalite = 0.0;
        foreach ($data as $row) {
            $totalCA       += (float)($row['pt'] ?? 0);
            $totalPenalite += (float)($row['montantPenalitePaye'] ?? 0);
        }

        // Préparation des données pour le graphique (CA + pénalité par jour)
        $byDay = [];
        foreach ($data as $row) {
            $d = $row['dateLocation'];
            if (!isset($byDay[$d])) {
                $byDay[$d] = ['ca' => 0.0, 'montantPenalitePaye' => 0.0];
            }
            $byDay[$d]['ca']       += (float)($row['pt'] ?? 0);
            $byDay[$d]['montantPenalitePaye'] += (float)($row['montantPenalitePaye'] ?? 0);
        }
        $chartLabels    = array_keys($byDay);
        $chartCA        = array_map(fn($d) => $d['ca'], $byDay);
        $chartPenalites = array_map(fn($d) => $d['montantPenalitePaye'], $byDay);
        ?>

        <!-- Résumé + Graphique -->
        <div class="bg-white border shadow rounded-xl p-4 mb-4">

            <h2 class="text-lg font-semibold mb-3">
                Résultats : du <?= htmlspecialchars($date1) ?> au <?= htmlspecialchars($date2) ?>
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                <div class="p-3 bg-slate-50 rounded-lg text-center">
                    <div class="text-xs text-slate-500">Total locations</div>
                    <div class="text-xl font-bold text-slate-800"><?= count($data) ?></div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg text-center">
                    <div class="text-xs text-slate-500">Chiffre d’affaires</div>
                    <div class="text-xl font-bold text-slate-800">
                        <?= number_format($totalCA, 2) ?>
                    </div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg text-center">
                    <div class="text-xs text-slate-500">Pénalités</div>
                    <div class="text-xl font-bold text-slate-800">
                        <?= number_format($totalPenalite, 2) ?>
                    </div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg text-center">
                    <div class="text-xs text-slate-500">Part des pénalités</div>
                    <div class="text-xl font-bold text-slate-800">
                        <?php
                        $ratio = $totalCA > 0 ? ($totalPenalite / $totalCA) * 100 : 0;
                        echo number_format($ratio, 1) . ' %';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Graphique -->
            <div class="w-full max-w-4xl mx-auto">
                <canvas id="chartCA"></canvas>
            </div>
        </div>

        <!-- Tableau détaillé -->
        <div class="bg-white border shadow rounded-xl p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b">
                    <tr class="text-left text-slate-600">
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Client</th>
                        <th class="px-3 py-2">PowerBank</th>
                        <th class="px-3 py-2">Quartier</th>
                        <th class="px-3 py-2">Agent</th>
                        <th class="px-3 py-2 text-right">Montant</th>
                        <th class="px-3 py-2 text-right">Pénalité</th>
                        <th class="px-3 py-2 text-center">Statut pénalité</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php foreach ($data as $row): ?>
                        <?php
                        $statut = $row['statutPenalite'] ?? 'aucune';
                        $badgeClass = 'bg-slate-100 text-slate-700';
                        $badgeText  = 'Aucune';

                        if ($statut === 'due') {
                            $badgeClass = 'bg-amber-100 text-amber-700';
                            $badgeText  = 'Due';
                        } elseif ($statut === 'paye') {
                            $badgeClass = 'bg-emerald-100 text-emerald-700';
                            $badgeText  = 'Payée';
                        } elseif ($statut === 'non_paye') {
                            $badgeClass = 'bg-red-100 text-red-700';
                            $badgeText  = 'Non payée';
                        }
                        ?>
                        <tr>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['dateLocation']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['clientNom']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['powerCode']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['quartierNom']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['agentNom']) ?></td>
                            <td class="px-3 py-2 text-right"><?= number_format((float)$row['pt'], 2) ?></td>
                            <td class="px-3 py-2 text-right"><?= number_format((float)$row['montantPenalitePaye'], 2) ?></td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-[11px] <?= $badgeClass ?>">
                                    <?= $badgeText ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('chartCA').getContext('2d');

            const labels   = <?= json_encode($chartLabels) ?>;
            const dataCA   = <?= json_encode($chartCA) ?>;
            const dataPen  = <?= json_encode($chartPenalites) ?>;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'CA',
                            data: dataCA,
                        },
                        {
                            label: 'Pénalités',
                            data: dataPen,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

    <?php endif; ?>

</div>
