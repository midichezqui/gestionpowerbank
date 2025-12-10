<?php
if (!isset($title)) {
    $title = 'Liste des clients';
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
        <h1 class="text-xl font-semibold text-slate-800"><?= htmlspecialchars($title) ?></h1>

        <!-- Tous les rôles (y compris agent simple) peuvent ajouter un client -->
        <a href="index.php?controller=client&action=create"
           class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow transition text-xs sm:text-sm">
            <i class="fa-solid fa-plus"></i>
            Ajouter
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

        <div class="overflow-x-auto max-w-full p-2 sm:p-4">
            <table id="clientTable" class="min-w-full text-xs sm:text-sm align-middle">
                <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-3 sm:px-4 py-2">Nom complet</th>
                    <th class="px-3 sm:px-4 py-2">Sexe</th>
                    <th class="px-3 sm:px-4 py-2">Quartier</th>
                    <th class="px-3 sm:px-4 py-2">Commune</th>
                    <th class="px-3 sm:px-4 py-2">Téléphone</th>
                    <th class="px-3 sm:px-4 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if (!empty($clients)) : ?>
                    <?php foreach ($clients as $cl) : ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 sm:px-4 py-2 font-medium text-slate-800">
                                <?= htmlspecialchars($cl->prenom . ' ' . $cl->nom . ' ' . $cl->postnom) ?>
                            </td>
                            <td class="px-3 sm:px-4 py-2">
                                <?= htmlspecialchars($cl->sexe) ?>
                            </td>
                            <td class="px-3 sm:px-4 py-2">
                                <?= htmlspecialchars($cl->quartierLabel ?? '') ?>
                            </td>
                            <td class="px-3 sm:px-4 py-2">
                                <?= htmlspecialchars($cl->communeLabel ?? '') ?>
                            </td>
                            <td class="px-3 sm:px-4 py-2">
                                <?= htmlspecialchars($cl->telephone) ?>
                            </td>
                            <td class="px-3 sm:px-4 py-2 text-center">
                                <div class="inline-flex gap-1 sm:gap-2">
                                    <!-- Détails : tous les rôles -->
                                    <a href="index.php?controller=client&action=edit&id=<?= $cl->id ?>"
                                       class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-md bg-sky-100 text-sky-700 hover:bg-sky-200 transition"
                                       title="Détails du client">
                                        <i class="fa-solid fa-eye text-[11px] sm:text-xs"></i>
                                    </a>

                                    <!-- Supprimer : seulement super (1) -->
                                    <?php if ($isSuper): ?>
                                        <a href="index.php?controller=client&action=delete&id=<?= $cl->id ?>"
                                           onclick="return confirm('Supprimer ce client ?');"
                                           class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition"
                                           title="Supprimer">
                                            <i class="fa-solid fa-trash text-[11px] sm:text-xs"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
    $(document).ready(function () {
        $('#clientTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                decimal: ",",
                thousands: ".",
                processing: "Traitement en cours...",
                search: "Rechercher&nbsp;:",
                lengthMenu: "Afficher _MENU_ éléments",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                infoEmpty: "Affichage de 0 à 0 sur 0 élément",
                infoFiltered: "(filtré de _MAX_ éléments au total)",
                loadingRecords: "Chargement...",
                zeroRecords: "Aucun élément à afficher",
                emptyTable: "Aucune donnée disponible",
                paginate: {
                    first: "Premier",
                    previous: "Précédent",
                    next: "Suivant",
                    last: "Dernier"
                },
                aria: {
                    sortAscending: ": activer pour trier la colonne par ordre croissant",
                    sortDescending: ": activer pour trier la colonne par ordre décroissant"
                }
            }
        });
    });
</script>
