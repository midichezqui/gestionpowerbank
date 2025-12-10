<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<div class="p-4 sm:p-6">
    <h1 class="text-xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-plug-circle-xmark text-sky-600"></i>
        Historique des PowerBanks affectés mais jamais loués
    </h1>

    <!-- Formulaire de filtre dates / agent / quartier (historique) -->
    <form method="get" class="bg-white shadow-sm border rounded-xl p-4 mb-4">
        <input type="hidden" name="controller" value="rapport">
        <input type="hidden" name="action" value="rapportHistoriqueNonLoue">

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            <div>
                <label class="text-xs text-slate-600 font-medium">Date début affectation</label>
                <input type="date" name="date1" required
                       value="<?= htmlspecialchars($date1 ?? '') ?>"
                       class="w-full px-3 py-2 rounded-lg border focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Date fin affectation</label>
                <input type="date" name="date2" required
                       value="<?= htmlspecialchars($date2 ?? '') ?>"
                       class="w-full px-3 py-2 rounded-lg border focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Agent</label>
                <select name="idAgent" class="w-full px-3 py-2 rounded-lg border text-sm focus:ring-sky-500 focus:border-sky-500">
                    <option value="">Tous</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= (int)$agent['id'] ?>" <?= (!empty($idAgent) && (int)$idAgent === (int)$agent['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($agent['nomComplet']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-600 font-medium">Quartier</label>
                <select name="idQuartier" class="w-full px-3 py-2 rounded-lg border text-sm focus:ring-sky-500 focus:border-sky-500">
                    <option value="">Tous</option>
                    <?php foreach ($quartiers as $quartier): ?>
                        <option value="<?= (int)$quartier['id'] ?>" <?= (!empty($idQuartier) && (int)$idQuartier === (int)$quartier['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($quartier['nomQuartier']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm w-full">
                    Rechercher
                </button>
            </div>
        </div>
    </form>

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <?= implode("<br>", $errors) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border shadow rounded-xl p-4">
        <?php if (empty($rows)): ?>
            <p class="text-sm text-slate-600">Aucune affectation sans location trouvée pour cette période.</p>
        <?php else: ?>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <p class="text-xs text-slate-500">
                    Période : du <?= htmlspecialchars($date1) ?> au <?= htmlspecialchars($date2) ?>
                    <?php if (!empty($idAgent)): ?>
                        — Agent filtré
                    <?php endif; ?>
                    <?php if (!empty($idQuartier)): ?>
                        — Quartier filtré
                    <?php endif; ?>
                </p>
                <div class="flex flex-wrap gap-2 text-xs">
                    <form method="get" action="index.php" class="inline-flex items-center gap-1">
                        <input type="hidden" name="controller" value="rapport">
                        <input type="hidden" name="action" value="rapportHistoriqueNonLoueCsv">
                        <input type="hidden" name="date1" value="<?= htmlspecialchars($date1) ?>">
                        <input type="hidden" name="date2" value="<?= htmlspecialchars($date2) ?>">
                        <input type="hidden" name="idAgent" value="<?= htmlspecialchars($idAgent ?? '') ?>">
                        <input type="hidden" name="idQuartier" value="<?= htmlspecialchars($idQuartier ?? '') ?>">
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-file-csv"></i>
                            <span>Export CSV</span>
                        </button>
                    </form>
                    <form method="get" action="index.php" class="inline-flex items-center gap-1">
                        <input type="hidden" name="controller" value="rapport">
                        <input type="hidden" name="action" value="rapportHistoriqueNonLouePdf">
                        <input type="hidden" name="date1" value="<?= htmlspecialchars($date1) ?>">
                        <input type="hidden" name="date2" value="<?= htmlspecialchars($date2) ?>">
                        <input type="hidden" name="idAgent" value="<?= htmlspecialchars($idAgent ?? '') ?>">
                        <input type="hidden" name="idQuartier" value="<?= htmlspecialchars($idQuartier ?? '') ?>">
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Export PDF</span>
                        </button>
                    </form>
                </div>
            </div>
            <?php
            // Calcul du score de "mauvais agent" : nombre de powerbanks jamais loués par agent
            $agentScores = [];
            foreach ($rows as $row) {
                $nom = $row['agentNom'] ?? '';
                if (!isset($agentScores[$nom])) {
                    $agentScores[$nom] = 0;
                }
                $agentScores[$nom]++;
            }
            arsort($agentScores);
            ?>

            <?php if (!empty($agentScores)): ?>
                <div class="mb-4 overflow-x-auto">
                    <table class="min-w-full text-xs sm:text-sm mb-2">
                        <thead class="bg-slate-50 border-b">
                        <tr class="text-left text-slate-600">
                            <th class="px-3 py-2">Agent</th>
                            <th class="px-3 py-2 text-right">Nombre de PowerBanks jamais loués</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php foreach ($agentScores as $nomAgent => $score): ?>
                            <tr>
                                <td class="px-3 py-2"><?= htmlspecialchars($nomAgent) ?></td>
                                <td class="px-3 py-2 text-right"><?= (int)$score ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-slate-50 border-b">
                    <tr class="text-left text-slate-600">
                        <th class="px-3 py-2">Date affectation</th>
                        <th class="px-3 py-2">PowerBank</th>
                        <th class="px-3 py-2">Agent</th>
                        <th class="px-3 py-2">Commune</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['dateAffectation']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['powerCode']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['agentNom']) ?></td>
                            <td class="px-3 py-2"><?= htmlspecialchars($row['communeNom'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
