<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$title  = $title  ?? 'Chiffre d’affaires';
$date1  = $date1  ?? date('Y-m-01');
$date2  = $date2  ?? date('Y-m-d');
$rows   = $rows   ?? [];
$errors = $errors ?? [];
$totals = $totals ?? [
    'nbLocations'       => 0,
    'totalCA'           => 0,
    'totalPenalite'     => 0,
    'totalSansPenalite' => 0,
];
?>
<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-emerald-600"></i>
                <span><?= htmlspecialchars($title) ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Vue d’ensemble du chiffre d’affaires entre les dates sélectionnées.
            </p>
        </div>
    </div>

    <!-- Formulaire de filtre -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-4">
        <form method="get" class="p-4 sm:p-5 space-y-3 sm:space-y-0 sm:flex sm:items-end sm:gap-4">
            <input type="hidden" name="controller" value="rapport">
            <input type="hidden" name="action" value="chiffreAffaires">

            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Date début
                </label>
                <input type="date" name="date1"
                       value="<?= htmlspecialchars($date1) ?>"
                       class="w-full px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Date fin
                </label>
                <input type="date" name="date2"
                       value="<?= htmlspecialchars($date2) ?>"
                       class="w-full px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                    <i class="fa-solid fa-magnifying-glass-chart text-xs"></i>
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <div>
                    <p class="font-semibold mb-1">Veuillez corriger :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($rows)): ?>

        <!-- Résumé chiffres clés -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-white border border-slate-100 rounded-xl p-4">
                <p class="text-xs text-slate-500">Nombre total de locations</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    <?= (int)($totals['nbLocations'] ?? 0) ?>
                </p>
            </div>

            <div class="bg-white border border-slate-100 rounded-xl p-4">
                <p class="text-xs text-slate-500">Chiffre d’affaires (total)</p>
                <p class="mt-1 text-xl font-bold text-emerald-700">
                    <?= number_format((float)($totals['totalCA'] ?? 0), 2) ?> FC
                </p>
            </div>

            <div class="bg-white border border-slate-100 rounded-xl p-4">
                <p class="text-xs text-slate-500">Total pénalités</p>
                <p class="mt-1 text-xl font-bold text-amber-600">
                    <?= number_format((float)($totals['totalPenalite'] ?? 0), 2) ?> FC
                </p>
            </div>

            <div class="bg-white border border-slate-100 rounded-xl p-4">
                <p class="text-xs text-slate-500">CA hors pénalités</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    <?= number_format((float)($totals['totalSansPenalite'] ?? 0), 2) ?> FC
                </p>
            </div>
        </div>

        <!-- Graphique & tableau -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <!-- Graphique -->
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <h2 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-chart-column"></i>
                    Evolution du CA par jour
                </h2>
                <canvas id="caChart" class="w-full h-64"></canvas>
            </div>

            <!-- Tableau -->
            <div class="bg-white border border-slate-200 rounded-xl p-4 overflow-x-auto">
                <h2 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-table"></i>
                    Détail quotidien
                </h2>

                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-slate-600">
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2 text-right"># Locations</th>
                        <th class="px-3 py-2 text-right">CA total</th>
                        <th class="px-3 py-2 text-right">Pénalités</th>
                        <th class="px-3 py-2 text-right">CA hors pénalités</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2">
                                <?= htmlspecialchars($r['jour']) ?>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <?= (int)($r['nbLocations'] ?? 0) ?>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <?= number_format((float)($r['totalCA'] ?? 0), 2) ?>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <?= number_format((float)($r['totalPenalite'] ?? 0), 2) ?>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <?= number_format((float)($r['totalSansPenalite'] ?? 0), 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

    <?php else: ?>

        <div class="bg-white border border-slate-200 rounded-xl p-6 text-center text-slate-500 text-sm">
            Aucun résultat pour la période sélectionnée.
        </div>

    <?php endif; ?>
</div>

<?php if (!empty($rows)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const ctx = document.getElementById('caChart').getContext('2d');

            const labels = <?= json_encode(array_column($rows, 'jour')) ?>;
            const dataCA = <?= json_encode(array_map('floatval', array_column($rows, 'totalCA'))) ?>;
            const dataPenalite = <?= json_encode(array_map('floatval', array_column($rows, 'totalPenalite'))) ?>;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'CA total',
                            data: dataCA,
                            borderWidth: 1
                        },
                        {
                            label: 'Pénalités',
                            data: dataPenalite,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })();
    </script>
<?php endif; ?>
