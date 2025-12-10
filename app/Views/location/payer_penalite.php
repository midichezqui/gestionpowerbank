<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/** @var Location $location */
/** @var float $montantRequis */
/** @var bool $clientBloque */

$errors        = $errors        ?? [];
$old           = $old           ?? [];
$montantRequis = $montantRequis ?? 0;
$clientBloque  = $clientBloque  ?? false;


function old_loc($name, $old, $default = '')
{
    return $old[$name] ?? $default;
}

$defaultDate = date('Y-m-d\TH:i');
?>
<div class="p-4 sm:p-6">

    <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- En-tête -->
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i>
                    <span>Paiement de la pénalité</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Cette opération enregistre le paiement de la pénalité
                    <?php if ($clientBloque): ?>
                        et débloque le client.
                    <?php else: ?>
                        (le client n'est pas bloqué pour l'instant).
                    <?php endif; ?>
                </p>

            </div>

            <a href="index.php?controller=location&action=index"
               class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                Retour
            </a>
        </div>

        <div class="px-4 sm:px-6 py-5 space-y-5">

            <!-- Erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <div>
                            <p class="font-semibold mb-1">Veuillez corriger :</p>
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Résumé location -->
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs sm:text-sm text-slate-700 space-y-1">
                <div><span class="font-semibold">Client :</span> <?= htmlspecialchars($location->clientNom ?? '') ?></div>
                <div><span class="font-semibold">PowerBank :</span> <?= htmlspecialchars($location->powerCode ?? '') ?></div>
                <div><span class="font-semibold">Date location :</span> <?= htmlspecialchars(substr($location->dateLocation, 0, 10)) ?></div>
                <div><span class="font-semibold">Durée :</span> <?= (int)$location->duree ?> h</div>
                
                <div>
                    <span class="font-semibold">Pénalité simple :</span>
                    <?= number_format((float)$location->penalite, 2) ?>
                </div>

                <div>
                    <span class="font-semibold">
                        <?php if ($clientBloque): ?>
                            Montant de déblocage (double pénalité) :
                        <?php else: ?>
                            Pénalité à encaisser :
                        <?php endif; ?>
                    </span>
                    <span class="text-amber-700 font-bold">
                        <?= number_format((float)$montantRequis, 2) ?>
                    </span>
                </div>

            </div>

            <!-- Formulaire paiement -->
            <form action="" method="post" class="space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Montant payé -->
                    <div class="grid gap-1">
                        <label class="text-xs font-medium text-slate-700">
                            Montant payé <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0"
                               name="montantPaye"
                               value="<?= htmlspecialchars(old_loc('montantPaye', $old, number_format($montantRequis, 2, '.', ''))) ?>"
                               class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               required>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Le montant payé doit être au moins égal au montant requis
                            (<?= number_format((float)$montantRequis, 2) ?>).
                        </p>
                    </div>

                    <!-- Date paiement -->
                    <div class="grid gap-1">
                        <label class="text-xs font-medium text-slate-700">
                            Date de paiement <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local"
                               name="datePaiement"
                               value="<?= htmlspecialchars(old_loc('datePaiement', $old, $defaultDate)) ?>"
                               class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               required>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                        <i class="fa-solid fa-unlock-keyhole text-xs"></i>
                        Confirmer le paiement & débloquer
                    </button>
                    <a href="index.php?controller=location&action=index"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        Annuler
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
