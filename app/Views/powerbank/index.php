<?php
if (!isset($title)) {
    $title = 'Liste des PowerBanks';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idFonction = $_SESSION['user_idFonction'] ?? null;
$isSuper = (int)$idFonction === 1;
$isAdmin = (int)$idFonction === 2;
?>

<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
       
        <?php if ($isSuper || $isAdmin): ?>
            <a href="index.php?controller=powerbank&action=create"
               class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow transition text-xs sm:text-sm">
                <i class="fa-solid fa-plus"></i>
                Ajouter
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

        <!-- Wrapper responsive: scroll horizontal sur petit écran -->
        <div class="overflow-x-auto max-w-full p-2 sm:p-4">
            <table id="powerbankTable"
           class="min-w-full text-xs sm:text-sm align-middle">
                <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-3 sm:px-4 py-2">#</th>
                    <th class="px-3 sm:px-4 py-2">Code</th>
                    <th class="px-3 sm:px-4 py-2">Capacité</th>
                    <th class="px-3 sm:px-4 py-2">Écran</th>
                    <th class="px-3 sm:px-4 py-2">Type câble</th>
                    <th class="px-3 sm:px-4 py-2">Commune</th>
                    <th class="px-3 sm:px-4 py-2">Statut</th>
                    <th class="px-3 sm:px-4 py-2">Tarif</th>
                    <th class="px-3 sm:px-4 py-2 text-center">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                <?php if (!empty($powerbanks)) : ?>
                    <?php foreach ($powerbanks as $pb) : ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->id) ?></td>
                            <td class="px-3 sm:px-4 py-2 font-medium text-slate-800"><?= htmlspecialchars($pb->codePower) ?></td>
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->capacite) ?></td>
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->presentationEcran) ?></td>
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->typeCableLibel ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->communeLibel ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-3 sm:px-4 py-2"><?= htmlspecialchars($pb->statutLibel ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            
                            <td class="px-3 sm:px-4 py-2 font-semibold text-slate-800">
                                <?= number_format($pb->tarif, 2) ?> CDF
                            </td>
                            <td class="px-3 sm:px-4 py-2 text-center">
                                <?php if ($isSuper || $isAdmin): ?>
                                    <div class="inline-flex gap-1 sm:gap-2">
                                        <!-- Modifier : admin (2) et super (1) -->
                                        <a href="index.php?controller=powerbank&action=edit&id=<?= $pb->id ?>"
                                           class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200 transition"
                                           title="Modifier">
                                            <i class="fa-solid fa-pen text-[11px] sm:text-xs"></i>
                                        </a>

                                        <!-- Supprimer : seulement super (1) -->
                                        <?php if ($isSuper): ?>
                                            <a href="index.php?controller=powerbank&action=delete&id=<?= $pb->id ?>"
                                               onclick="return confirm('Voulez-vous vraiment supprimer ce PowerBank ?');"
                                               class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition"
                                               title="Supprimer">
                                                <i class="fa-solid fa-trash text-[11px] sm:text-xs"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>


<!-- Initialisation DataTables -->
<script>
    $(document).ready(function () {
        $('#powerbankTable').DataTable({
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

