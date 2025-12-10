<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<div class="p-4 sm:p-6">
    <h1 class="text-xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-plug-circle-xmark text-sky-600"></i>
        PowerBanks affectés mais jamais loués
    </h1>

    

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <?= implode("<br>", $errors) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($libere)): ?>
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm">
            Tous les PowerBanks non loués sur cette période ont été remis à l'état libre.
        </div>
    <?php endif; ?>

    <div class="bg-white border shadow rounded-xl p-4">
        <?php if (empty($rows)): ?>
            <p class="text-sm text-slate-600">Aucune affectation sans location trouvée pour cette période.</p>
        <?php else: ?>
            <p class="text-xs text-slate-500 mb-3">
                Période : du <?= htmlspecialchars($date1) ?> au <?= htmlspecialchars($date2) ?>
            </p>
            <form method="post" action="index.php?controller=rapport&action=libererPowerbanksNonLoue" class="mb-3">
                <input type="hidden" name="date1" value="<?= htmlspecialchars($date1) ?>">
                <input type="hidden" name="date2" value="<?= htmlspecialchars($date2) ?>">
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs shadow">
                    <i class="fa-solid fa-unlock"></i>
                    Libérer tous ces PowerBanks
                </button>
            </form>
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
