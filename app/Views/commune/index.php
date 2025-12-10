<?php
if (!isset($title)) $title = 'Liste des communes';
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800"><?= htmlspecialchars($title) ?></h1>

        <a href="index.php?controller=commune&action=create"
           class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm">
            <i class="fa-solid fa-plus"></i>
            Ajouter
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto p-2 sm:p-4">
            <table id="communeTable" class="min-w-full text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Commune</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                <?php foreach ($communes as $c) : ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2"><?= $c->id ?></td>
                        <td class="px-4 py-2 font-medium text-slate-800"><?= htmlspecialchars($c->nomCommune) ?></td>

                        <td class="px-4 py-2 text-center">
                            <div class="inline-flex gap-2">
                                <a href="index.php?controller=commune&action=edit&id=<?= $c->id ?>"
                                   class="w-8 h-8 flex items-center justify-center rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <a href="index.php?controller=commune&action=delete&id=<?= $c->id ?>"
                                   onclick="return confirm('Supprimer cette commune ?');"
                                   class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-700 hover:bg-red-200">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#communeTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: "Rechercher:",
            lengthMenu: "Afficher _MENU_ lignes",
            info: "Affichage _START_ à _END_ sur _TOTAL_",
            paginate: {
                next: "Suivant",
                previous: "Précédent"
            }
        }
    });
});
</script>
