<?php
if (!isset($title)) $title = 'Historique des pénalités';
if (session_status() === PHP_SESSION_NONE) session_start();
$locations = $locations ?? [];
?>
<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-amber-600"></i>
                <span><?= htmlspecialchars($title) ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Liste des locations ayant généré une pénalité (due, non payée ou payée).
            </p>
        </div>

        <a href="index.php?controller=location&action=index"
           class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            Retour aux locations
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto p-2 sm:p-4">
            <table class="min-w-full text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Client</th>
                    <th class="px-4 py-2">PowerBank</th>
                    <th class="px-4 py-2">Durée</th>
                    <th class="px-4 py-2 text-right">Montant loc.</th>
                    <th class="px-4 py-2 text-right">Montant payé</th>
                    <th class="px-4 py-2 text-center">Statut pénalité</th>
                    <th class="px-4 py-2">Date paiement</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if (!empty($locations)): ?>
                    <?php foreach ($locations as $loc): ?>
                        <?php
                        $sp = $loc->statutPenalite ?? 'aucune';
                        $label = 'Aucune';
                        $badge = 'bg-slate-100 text-slate-700';

                        if ($sp === 'due') {
                            $label = 'Due';
                            $badge = 'bg-amber-100 text-amber-700';
                        } elseif ($sp === 'non_paye') {
                            $label = 'Non payée';
                            $badge = 'bg-red-100 text-red-700';
                        } elseif ($sp === 'paye') {
                            $label = 'Payée';
                            $badge = 'bg-emerald-100 text-emerald-700';
                        }
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2">
                                <?= htmlspecialchars(substr($loc->dateLocation, 0, 10)) ?>
                            </td>
                            <td class="px-4 py-2">
                                <?= htmlspecialchars($loc->clientNom ?? '') ?>
                            </td>
                            <td class="px-4 py-2">
                                <?= htmlspecialchars($loc->powerCode ?? '') ?>
                            </td>
                            <td class="px-4 py-2">
                                <?= (int)$loc->duree ?> h
                            </td>
                            <td class="px-4 py-2 text-right">
                                <?= number_format((float)$loc->pt, 2) ?>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <?= number_format((float)$loc->montantPenalitePaye, 2) ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-[11px] <?= $badge ?>">
                                    <?= htmlspecialchars($label) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <?= $loc->datePaiementPenalite
                                    ? htmlspecialchars($loc->datePaiementPenalite)
                                    : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-slate-500 text-sm">
                            Aucune pénalité enregistrée.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
