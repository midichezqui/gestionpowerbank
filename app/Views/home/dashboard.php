<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$title = $title ?? 'Tableau de bord';

// Variables envoyées par le controller
$powerbanksDisponibles = $powerbanksDisponibles ?? 0;
$locationsEnCours      = $locationsEnCours      ?? 0;
$retards               = $retards               ?? 0;
$recettesJour          = $recettesJour          ?? 0.0;
$lastLocations         = $lastLocations         ?? [];
?>

<div class="min-h-screen flex">

    <!-- Overlay mobile -->
    <div id="mobile-backdrop"
         class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"></div>

    <!-- Contenu principal -->
    <main class="flex-1 flex flex-col md:ml-0">

        <!-- Contenu -->
        <section class="flex-1 p-4 sm:p-6 space-y-6">

            <!-- Cards statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-sky-100 flex items-center justify-center">
                        <i class="fa-solid fa-battery-full text-sky-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">PowerBanks disponibles</p>
                        <p class="text-xl font-bold text-slate-800">
                            <?= (int)$powerbanksDisponibles ?>
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fa-solid fa-plug-circle-bolt text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Locations en cours</p>
                        <p class="text-xl font-bold text-slate-800">
                            <?= (int)$locationsEnCours ?>
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Retards</p>
                        <p class="text-xl font-bold text-slate-800">
                            <?= (int)$retards ?>
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fa-solid fa-coins text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Recettes du jour</p>
                        <p class="text-xl font-bold text-slate-800">
                            <?= number_format((float)$recettesJour, 2) ?> FC
                        </p>
                    </div>
                </div>
            </div>

            <!-- Deux colonnes: dernières locations + actions rapides -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

                <!-- Tableau dernières locations -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-list"></i>
                            Dernières locations
                        </h3>
                        <a href="index.php?controller=location&action=index"
                           class="text-xs text-sky-600 hover:text-sky-700 font-medium">
                            Voir tout
                        </a>
                    </div>
                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-100">
                                <th class="py-2 pr-4">Client</th>
                                <th class="py-2 pr-4">PowerBank</th>
                                <th class="py-2 pr-4">Départ</th>
                                <th class="py-2 pr-4">Retour prévu</th>
                                <th class="py-2 pr-4 text-center">Statut</th>
                            </tr>
                            </thead>
                            <tbody class="text-slate-700">
                            <?php if (!empty($lastLocations)): ?>
                                <?php foreach ($lastLocations as $loc): ?>
                                    <?php
                                    // Date début
                                    $dateBrute = $loc['dateLocation'];              // ex: "2025-11-27 00:00:00"
                                    $onlyDate  = substr($dateBrute, 0, 10);        // "2025-11-27"
                                    $heureDebut = $loc['heureDebut'] ?? '00:00:00';

                                    $startTs = strtotime($onlyDate . ' ' . $heureDebut);
                                    $retourPrevTs = $startTs + 4 * 3600;
                                    $retourPrev = date('H:i', $retourPrevTs);

                                    $now = time();

                                    $statutTexte = 'En cours';
                                    $badgeClass  = 'bg-emerald-100 text-emerald-700';

                                    if ($loc['statut'] === 'cloturee') {
                                        $statutTexte = 'Terminée';
                                        $badgeClass  = 'bg-slate-100 text-slate-700';
                                    } elseif ($loc['statut'] === 'demarree' && $now > $retourPrevTs) {
                                        $statutTexte = 'En retard';
                                        $badgeClass  = 'bg-amber-100 text-amber-700';
                                    }
                                    ?>
                                    <tr class="border-b border-slate-50">
                                        <td class="py-2 pr-4">
                                            <?= htmlspecialchars($loc['clientNom'] ?? '') ?>
                                        </td>
                                        <td class="py-2 pr-4">
                                            <?= htmlspecialchars($loc['powerCode'] ?? '') ?>
                                        </td>
                                        <td class="py-2 pr-4">
                                            <?= htmlspecialchars(substr($heureDebut, 0, 5)) ?>
                                        </td>
                                        <td class="py-2 pr-4">
                                            <?= htmlspecialchars($retourPrev) ?>
                                        </td>
                                        <td class="py-2 pr-4 text-center">
                                            <span class="inline-flex px-2 py-1 rounded-full text-[11px] <?= $badgeClass ?>">
                                                <?= htmlspecialchars($statutTexte) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400">
                                        Aucune location récente.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                        <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            Actions rapides
                        </h3>
                        <div class="space-y-2">
                            <a href="index.php?controller=location&action=create"
                               class="w-full inline-flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium bg-sky-600 text-white hover:bg-sky-700">
                                <span>Nouvelle location</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="index.php?controller=client&action=create"
                               class="w-full inline-flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium bg-slate-900 text-white hover:bg-slate-800">
                                <span>Ajouter un client</span>
                                <i class="fa-solid fa-user-plus"></i>
                            </a>
                            <a href="index.php?controller=powerbank&action=create"
                               class="w-full inline-flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700">
                                <span>Ajouter un PowerBank</span>
                                <i class="fa-solid fa-battery-half"></i>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-xs text-slate-600">
                        <h3 class="text-sm font-semibold text-slate-800 mb-2">
                            Astuce
                        </h3>
                        <p>
                            Vérifiez régulièrement les locations en retard pour relancer les clients
                            et optimiser la rotation de vos PowerBanks.
                        </p>
                    </div>
                </div>

            </div>

        </section>
    </main>

</div>
