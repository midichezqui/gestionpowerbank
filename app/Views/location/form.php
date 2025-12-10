<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$mode        = $mode ?? 'create';
$isEdit      = false; // ici on ne gère que "create" pour l’instant
$clients     = $clients      ?? [];
$affectations= $affectations ?? [];
$errors      = $errors       ?? [];
$old         = $old          ?? [];

$dateLocation = $dateLocation ?? date('Y-m-d');

$title = $title ?? 'Nouvelle location';

function old_val_loc($name, $old, $default = '')
{
    return $old[$name] ?? $default;
}
?>

<div class="min-h-[calc(100vh-80px)] bg-slate-50 px-3 py-4 sm:px-4 sm:py-6">
    <div class="w-full max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- HEADER -->
        <div class="px-4 sm:px-6 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-base sm:text-xl font-bold text-slate-900 flex items-center gap-2">
                        <span class="inline-flex w-9 h-9 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                            <i class="fa-solid fa-file-circle-plus text-sm"></i>
                        </span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Démarrer une nouvelle location de PowerBank pour un client.
                    </p>
                </div>
                <a href="index.php?controller=location&action=index"
                   class="inline-flex items-center justify-center gap-2 text-xs sm:text-sm px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Retour à la liste</span>
                </a>
            </div>
        </div>

        <!-- CONTENU -->
        <div class="px-4 sm:px-6 py-4 sm:py-5 space-y-4 sm:space-y-5">

            <!-- MESSAGES -->
            <?php if (!empty($errors)): ?>
                <div class="rounded-xl border border-red-200 bg-red-50 px-3 sm:px-4 py-3 text-xs sm:text-sm text-red-700">
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5 sm:mt-1">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
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

            <?php if (empty($clients)): ?>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 sm:px-4 py-3 text-[11px] sm:text-xs text-amber-800">
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5">
                            <i class="fa-solid fa-circle-info"></i>
                        </span>
                        <p>
                            Aucun client trouvé dans les quartiers concernés par vos affectations pour la date
                            <strong><?= htmlspecialchars($today) ?></strong>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FORMULAIRE -->
            <form action="" method="post" class="space-y-5">

                <!-- SECTION 1 : Client + Affectation -->
                <section class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-xs font-semibold">
                            1
                        </span>
                        <h2 class="text-sm sm:text-base font-semibold text-slate-800">
                            Informations principales
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Client -->
                        <div class="grid gap-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                Client <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] text-slate-400">
                                Seuls les clients actifs sont affichés.
                            </p>
                            <select name="idClient"
                                    class="px-3 py-2 border rounded-lg text-xs sm:text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 min-h-[40px]"
                                    required <?= empty($clients) ? 'disabled' : '' ?>>
                                <option value="">Sélectionner un client</option>
                                <?php
                                $selectedClient = old_val_loc('idClient', $old, '');
                                foreach ($clients as $cl):
                                    $id        = (string)$cl['id'];
                                    $nomComplet = trim(($cl['prenom'] ?? '') . ' ' . ($cl['nom'] ?? ''));
                                    $quartier   = $cl['nomQuartier'] ?? '';
                                    $label      = $nomComplet;
                                    if ($quartier !== '') {
                                        $label .= ' – ' . $quartier;
                                    }
                                ?>
                                    <option value="<?= htmlspecialchars($id) ?>"
                                        <?= $id === (string)$selectedClient ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Affectation -->
                        <div class="grid gap-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                PowerBank <span class="text-red-500">*</span>
                            </label>
                            <p class="text-[11px] text-slate-400">
                                Seules vos affectations pour la date
                                <span class="font-semibold"><?= htmlspecialchars($dateLocation) ?></span> sont affichées.
                            </p>
                            <select name="idAffectation"
                                    class="px-3 py-2 border rounded-lg text-xs sm:text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 min-h-[40px]"
                                    required>
                                <option value="">Sélectionner PowerBank</option>
                                <?php
                                $selectedAff = old_val_loc('idAffectation', $old, '');
                                foreach ($affectations as $af):
                                    $id   = (string)$af['id'];
                                    $txt  = $af['powerCode'];
                                ?>
                                    <option value="<?= htmlspecialchars($id) ?>"
                                        <?= $id === (string)$selectedAff ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($txt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2 : Horaires -->
                <section class="space-y-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-semibold">
                            2
                        </span>
                        <h2 class="text-sm sm:text-base font-semibold text-slate-800">
                            Horaires
                        </h2>
                    </div>

                    <p class="text-[11px] sm:text-xs text-slate-500">
                        La durée réelle et l’heure de fin seront calculées automatiquement à la clôture de la location.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="grid gap-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                Date de location
                            </label>
                            <input type="date"
                                   name="dateLocation"
                                   readonly
                                   value="<?= htmlspecialchars(old_val_loc('dateLocation', $old, $dateLocation)) ?>"
                                   class="px-3 py-2 border rounded-lg text-xs sm:text-sm bg-slate-50 text-slate-700 focus:ring-1 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div class="grid gap-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                Heure de début
                            </label>
                            <input type="time"
                                   name="heureDebut"
                                   readonly
                                   value="<?= htmlspecialchars(old_val_loc('heureDebut', $old, date('H:i'))) ?>"
                                   class="px-3 py-2 border rounded-lg text-xs sm:text-sm bg-slate-50 text-slate-700 focus:ring-1 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>
                </section>

                <!-- BOUTONS -->
                <div class="pt-4 border-t border-slate-100 mt-1 flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Démarrer la location</span>
                    </button>
                    <a href="index.php?controller=location&action=index"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Annuler</span>
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
