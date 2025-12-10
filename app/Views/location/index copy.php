<?php
if (!isset($title)) $title = 'Locations de PowerBank';
if (session_status() === PHP_SESSION_NONE) session_start();

$role        = $role        ?? 'simple';
$isSuperAdmin = $isSuperAdmin ?? false;
$canDelete   = $canDelete   ?? false;
$canClose    = $canClose    ?? true;
// On s'assure d'avoir un rôle (simple/admin/super) passé par le contrôleur
//$role = $role ?? 'simple';
?>
<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-plug-circle-bolt text-sky-600"></i>
                <span><?= htmlspecialchars($title) ?></span>
            </h1>

            <?php if ($role === 'super'): ?>
                <p class="text-xs text-emerald-600 mt-1">
                    Vous voyez toutes les locations (toutes dates, tous statuts).
                </p>
            <?php elseif ($role === 'admin'): ?>
                <p class="text-xs text-slate-500 mt-1">
                    Vous voyez les locations du jour, non clôturées, effectuées par vos agents affectés.
                </p>
            <?php else: ?>
                <p class="text-xs text-slate-500 mt-1">
                    Vous voyez vos propres locations du jour qui ne sont pas encore clôturées.
                </p>
            <?php endif; ?>
        </div>
        <?php if ($role === 'simple'): ?>
        <a href="index.php?controller=location&action=create"
           class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm">
            <i class="fa-solid fa-plus"></i>
            Nouvelle location
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto p-2 sm:p-4">

            <!-- WRAPPER GLOBAL -->
<div class="w-full">

    <!-- ========== VERSION DESKTOP : TABLEAU CLASSIQUE ========== -->
    <div class="hidden md:block overflow-x-auto">
        <!-- TABLE UNIQUE -->
        <table id="locationTable" class="min-w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-slate-600">
                <th class="px-4 py-2">Date</th>              <!-- 1 -->
                <th class="px-4 py-2">Client</th>            <!-- 2 -->
                <th class="px-4 py-2">PowerBank</th>         <!-- 3 -->
                <th class="px-4 py-2">Quartier</th>          <!-- 4 -->
                <th class="px-4 py-2">Début</th>             <!-- 5 -->
                <th class="px-4 py-2">Fin</th>               <!-- 6 -->
                <th class="px-4 py-2 text-center">Durée (h)</th><!-- 7 -->
                <th class="px-4 py-2 text-right">Montant</th><!-- 8 -->
                <th class="px-4 py-2 text-right">Pénalité</th><!-- 9 -->
                <th class="px-4 py-2 text-center">Statut</th><!-- 10 -->
                <th class="px-4 py-2 text-center">Actions</th><!-- 11 -->
            </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
            <?php if (!empty($locations)) : ?>
                <?php foreach ($locations as $loc) :
                    $isClosed = ($loc->statut === 'cloturee');
                ?>
                    <tr class="hover:bg-slate-50">
                        <!-- 1: Date -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars(substr($loc->dateLocation, 0, 10)) ?>
                        </td>

                        <!-- 2: Client -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($loc->clientNom ?? '') ?>
                        </td>

                        <!-- 3: PowerBank -->
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                <i class="fa-solid fa-battery-half text-[11px]"></i>
                                <?= htmlspecialchars($loc->powerCode ?? '') ?>
                            </span>
                        </td>

                        <!-- 4: Quartier -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($loc->quartierNom ?? '') ?>
                        </td>

                        <!-- 5: Début -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($loc->heureDebut) ?>
                        </td>

                        <!-- 6: Fin -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($loc->heureFin) ?>
                        </td>

                        <!-- 7: Durée -->
                        <td class="px-4 py-2 text-center font-medium">
                            <?= (int)$loc->duree ?>
                        </td>

                        <!-- 8: Montant -->
                        <td class="px-4 py-2 text-right font-semibold text-slate-800">
                            <?= number_format((float)$loc->pt, 2) ?>
                        </td>

                        <!-- 9: Pénalité -->
                        <td class="px-4 py-2 text-right">
                            <?= number_format((float)$loc->penalite, 2) ?>
                        </td>

                        <!-- 10: Statut -->
                        <td class="px-4 py-2 text-center">
                            <?php if ($loc->statut === 'cloturee'): ?>
                                <span class="inline-flex px-2 py-1 rounded-full text-[11px] bg-emerald-100 text-emerald-700">
                                    Clôturée
                                </span>
                            <?php else: ?>
                                <span class="inline-flex px-2 py-1 rounded-full text-[11px] bg-sky-100 text-sky-700">
                                    Démarrée
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- 11: Actions -->
                        <td class="px-4 py-2 text-center">
                            <div class="inline-flex flex-wrap gap-2 justify-center">

                                <?php if (!$isClosed && $role === 'simple'): ?>
                                    <!-- Seuls les agents simples peuvent clôturer -->
                                    <a href="index.php?controller=location&action=close&id=<?= $loc->id ?>"
                                    onclick="return confirm('Clôturer cette location ?');"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                                    title="Clôturer">
                                        <i class="fa-solid fa-flag-checkered text-xs"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($role === 'simple' && $loc->statutPenalite === 'due'): ?>
                                    <a href="index.php?controller=location&action=marquerNonPaye&id=<?= $loc->id ?>"
                                    onclick="return confirm('Confirmer que le client n\'a pas payé et le bloquer ?');"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200"
                                    title="Bloquer le client">
                                        <i class="fa-solid fa-user-lock text-xs"></i>
                                    </a>
                                <?php endif; ?>


                                <?php if (($role === 'admin' || $role === 'super') && $loc->penalite > 0): ?>
                                    <!-- Paiement pénalité (admin / super admin) -->
                                    <a href="index.php?controller=location&action=payerPenalite&id=<?= $loc->id ?>"
                                    class="inline-flex items-center justify-center px-2 h-8 rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200 text-[11px]"
                                    title="Payer la pénalité">
                                        <i class="fa-solid fa-coins mr-1 text-[10px]"></i>
                                        Pénalité
                                    </a>
                                <?php endif; ?>

                                <?php if ($role === 'super'): ?>
                                    <a href="index.php?controller=location&action=delete&id=<?= $loc->id ?>"
                                    onclick="return confirm('Supprimer cette location ?');"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200"
                                    title="Supprimer">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                
            <?php endif; ?>
            </tbody>
        </table>
        <!-- FIN TABLE -->
    </div>

    <!-- ========== VERSION MOBILE : CARTES (NAVIGATION FACILITÉE) ========== -->
        <!-- ========== VERSION MOBILE : CARTES (NAVIGATION FACILITÉE) ========== -->
    <div class="space-y-3 md:hidden">
        <?php if (!empty($locations)) : ?>
            <?php foreach ($locations as $loc) :
                $isClosed = ($loc->statut === 'cloturee');
            ?>
                <div class="border rounded-lg p-3 bg-white shadow-sm">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <p class="font-semibold text-sm">
                                <?= htmlspecialchars($loc->clientNom ?? 'Client') ?>
                            </p>
                            <p class="text-[11px] text-slate-500">
                                <?= htmlspecialchars($loc->quartierNom ?? '') ?>
                            </p>
                            <p class="text-[11px] text-slate-500 mt-1">
                                <span class="font-medium">Date :</span>
                                <?= htmlspecialchars(substr($loc->dateLocation, 0, 10)) ?>
                            </p>
                        </div>

                        <div class="text-right space-y-1">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px]">
                                <i class="fa-solid fa-battery-half text-[10px]"></i>
                                <?= htmlspecialchars($loc->powerCode ?? '') ?>
                            </span>

                            <div>
                                <?php if ($loc->statut === 'cloturee'): ?>
                                    <span class="inline-flex mt-1 px-2 py-1 rounded-full text-[10px] bg-emerald-100 text-emerald-700">
                                        Clôturée
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex mt-1 px-2 py-1 rounded-full text-[10px] bg-sky-100 text-sky-700">
                                        Démarrée
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-[11px] text-slate-700">
                        <p>
                            <span class="font-semibold">Début :</span>
                            <span class="ml-1"><?= htmlspecialchars($loc->heureDebut) ?></span>
                        </p>
                        <p>
                            <span class="font-semibold">Fin :</span>
                            <span class="ml-1"><?= htmlspecialchars($loc->heureFin) ?></span>
                        </p>
                        <p>
                            <span class="font-semibold">Durée :</span>
                            <span class="ml-1"><?= (int)$loc->duree ?> h</span>
                        </p>
                        <p>
                            <span class="font-semibold">Montant :</span>
                            <span class="ml-1">
                                <?= number_format((float)$loc->pt, 2) ?>
                            </span>
                        </p>
                        <p>
                            <span class="font-semibold">Pénalité :</span>
                            <span class="ml-1">
                                <?= number_format((float)$loc->penalite, 2) ?>
                            </span>
                        </p>
                    </div>

                    <div class="mt-3 flex flex-wrap justify-end gap-2">
                        <?php if (!$isClosed && $role === 'simple'): ?>
                            <!-- Clôture (agents seulement) -->
                            <a href="index.php?controller=location&action=close&id=<?= $loc->id ?>"
                               onclick="return confirm('Clôturer cette location ?');"
                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-emerald-100 text-emerald-700 hover:bg-emerald-200 text-[11px]">
                                <i class="fa-solid fa-flag-checkered text-[10px]"></i>
                                Clôturer
                            </a>
                        <?php endif; ?>

                        <?php if ($loc->penalite > 0 && $role === 'simple'): ?>
                            <!-- Client n'a pas payé (agents) -->
                            <a href="index.php?controller=location&action=marquerNonPaye&id=<?= $loc->id ?>"
                               onclick="return confirm('Confirmer que le client N\'A PAS payé la pénalité et le BLOQUER ?');"
                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200 text-[11px]">
                                <i class="fa-solid fa-user-lock text-[10px]"></i>
                                Non payé
                            </a>
                        <?php endif; ?>

                        <?php if (($role === 'admin' || $role === 'super') && (float)$loc->penalite > 0): ?>
                            <!-- Encaissement pénalité (admin / super admin) -->
                            <a href="index.php?controller=location&action=payerPenalite&id=<?= $loc->id ?>"
                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200 text-[11px]">
                                <i class="fa-solid fa-coins text-[10px]"></i>
                                Pénalité
                            </a>
                        <?php endif; ?>

                        <?php if ($role === 'super'): ?>
                            <!-- Suppression (super admin) -->
                            <a href="index.php?controller=location&action=delete&id=<?= $loc->id ?>"
                               onclick="return confirm('Supprimer cette location ?');"
                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200 text-[11px]">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                                Supprimer
                            </a>
                        <?php endif; ?>

                        <?php if ($isClosed && $loc->penalite <= 0 && $role !== 'super'): ?>
                            <span class="text-[11px] text-slate-400 italic">
                                Aucune action
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="px-4 py-4 text-center text-slate-500 text-sm">
                Aucune location trouvée.
            </div>
        <?php endif; ?>
    </div>


</div>

        </div>
    </div>
</div>

<!-- Initialisation DataTables pour locationTable -->
<script>
    $(document).ready(function () {
        $('#locationTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                decimal:        ",",
                thousands:      ".",
                processing:     "Traitement en cours...",
                search:         "Rechercher&nbsp;:",
                lengthMenu:     "Afficher _MENU_ éléments",
                info:           "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                infoEmpty:      "Affichage de 0 à 0 sur 0 élément",
                infoFiltered:   "(filtré de _MAX_ éléments au total)",
                loadingRecords: "Chargement...",
                zeroRecords:    "Aucun élément à afficher",
                emptyTable:     "Aucune donnée disponible",
                paginate: {
                    first:      "Premier",
                    previous:   "Précédent",
                    next:       "Suivant",
                    last:       "Dernier"
                },
                aria: {
                    sortAscending:  ": activer pour trier la colonne par ordre croissant",
                    sortDescending: ": activer pour trier la colonne par ordre décroissant"
                }
            }
        });
    });
</script>
