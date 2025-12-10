<?php
if (!isset($title)) {
    $title = 'Liste des affectations';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role           = $role           ?? 'simple';
$isSuperAdmin   = $isSuperAdmin   ?? false;
$canCreate      = $canCreate      ?? false;
$canDelete      = $canDelete      ?? false;
$showCreatorCol = $showCreatorCol ?? false;
?>
<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-sky-600"></i>
                <span><?= htmlspecialchars($title) ?></span>
            </h1>

            <?php if ($role === 'super'): ?>
                <p class="text-xs text-emerald-600 mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-crown text-[10px]"></i>
                    Vous êtes connecté en tant que <strong>Super administrateur</strong> : toutes les affectations sont visibles,
                    avec le nom de l'agent qui les a créées.
                </p>
            <?php elseif ($role === 'admin'): ?>
                <p class="text-xs text-slate-500 mt-1">
                    Vous voyez les affectations que vous avez créées.
                </p>
            <?php else: ?>
                <p class="text-xs text-slate-500 mt-1">
                    Vous voyez uniquement les affectations qui vous sont assignées pour la date du jour.
                </p>
            <?php endif; ?>
        </div>

        <?php if ($canCreate): ?>
            <a href="index.php?controller=affectation&action=create"
               class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm">
                <i class="fa-solid fa-plus"></i>
                Nouvelle affectation
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="p-2 sm:p-4 overflow-x-auto">

            <div class="w-full">

    <!-- ========== VERSION DESKTOP : TABLEAU CLASSIQUE ========== -->
    <div class="hidden md:block overflow-x-auto">
        <table id="affectationTable" class="min-w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-slate-600">
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Agent affecté</th>
                <th class="px-4 py-2">PowerBank</th>
                <?php if ($showCreatorCol): ?>
                    <th class="px-4 py-2">Créée par</th>
                <?php endif; ?>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
            <?php if (!empty($affectations)) : ?>
                <?php foreach ($affectations as $item) : ?>
                    <tr class="hover:bg-slate-50">
                        <!-- Date -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars(substr($item->dateAffectation, 0, 10)) ?>
                        </td>

                        <!-- Agent affecté -->
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($item->agentNom ?? '') ?>
                        </td>

                        <!-- PowerBank -->
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                <i class="fa-solid fa-battery-half text-[11px]"></i>
                                <?= htmlspecialchars($item->powerCode ?? '') ?>
                            </span>
                        </td>

                        <!-- Créée par (super admin seulement) -->
                        <?php if ($showCreatorCol): ?>
                            <td class="px-4 py-2 text-xs">
                                <?php if (!empty($item->agentCreateNom)): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700">
                                        <i class="fa-solid fa-user-shield text-[10px]"></i>
                                        <?= htmlspecialchars($item->agentCreateNom) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">N/A</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <!-- Actions -->
                        <td class="px-4 py-2 text-center">
                            <div class="inline-flex gap-2 justify-center">
                                <?php if ($canDelete): ?>
                                    <a href="index.php?controller=affectation&action=delete&id=<?= $item->id ?>"
                                       onclick="return confirm('Supprimer cette affectation ?');"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200"
                                       title="Supprimer">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-400 italic">
                                        Aucune action
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
               
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ========== VERSION MOBILE : CARTES ========== -->
    <div class="space-y-3 md:hidden">
        <?php if (!empty($affectations)) : ?>
            <?php foreach ($affectations as $item) : ?>
                <div class="border rounded-lg p-3 bg-white shadow-sm">
                    <!-- Ligne du haut : agent + date -->
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <p class="font-semibold text-sm">
                                <?= htmlspecialchars($item->agentNom ?? 'Agent') ?>
                            </p>
                            <p class="text-[11px] text-slate-500 mt-1">
                                <span class="font-medium">Date :</span>
                                <?= htmlspecialchars(substr($item->dateAffectation, 0, 10)) ?>
                            </p>
                        </div>

                        <div class="text-right space-y-1">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px]">
                                <i class="fa-solid fa-battery-half text-[10px]"></i>
                                <?= htmlspecialchars($item->powerCode ?? '') ?>
                            </span>

                            <?php if ($showCreatorCol): ?>
                                <div class="text-[10px] text-slate-500">
                                    <?php if (!empty($item->agentCreateNom)): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-50 text-slate-600">
                                            <i class="fa-solid fa-user-shield text-[9px]"></i>
                                            <?= htmlspecialchars($item->agentCreateNom) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="italic">Créée par : N/A</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Zone actions -->
                    <div class="mt-3 flex justify-end">
                        <?php if ($canDelete): ?>
                            <a href="index.php?controller=affectation&action=delete&id=<?= $item->id ?>"
                               onclick="return confirm('Supprimer cette affectation ?');"
                               class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200 text-[11px]">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                                Supprimer
                            </a>
                        <?php else: ?>
                            <span class="text-[11px] text-slate-400 italic">
                                Aucune action
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="px-4 py-4 text-center text-slate-500 text-sm">
                Aucune affectation trouvée.
            </div>
        <?php endif; ?>
    </div>

</div>


        </div>
    </div>
</div>

<!-- Initialisation DataTables -->
<script>
    $(document).ready(function () {
        $('#affectationTable').DataTable({
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
