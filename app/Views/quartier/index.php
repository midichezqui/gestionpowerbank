<?php
if (!isset($title)) $title = 'Liste des quartiers';
if (session_status() === PHP_SESSION_NONE) session_start();

$idFonction = $_SESSION['user_idFonction'] ?? null;
$isSuper = (int)$idFonction === 1;
$isAdmin = (int)$idFonction === 2;
?>

<div class="p-4 sm:p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800"><?= htmlspecialchars($title) ?></h1>

        <?php if ($isSuper || $isAdmin): ?>
            <a href="index.php?controller=quartier&action=create"
               class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg shadow text-sm">
                <i class="fa-solid fa-plus"></i>
                Ajouter
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

        <div class="overflow-x-auto p-2 sm:p-4">
            <table id="quartierTable" class="min-w-full text-xs sm:text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Quartier</th>
                    <th class="px-4 py-2">Commune</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($quartiers as $q) : ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2"><?= htmlspecialchars($q->id) ?></td>
                        <td class="px-4 py-2 font-medium text-slate-800"><?= htmlspecialchars($q->nomQuartier) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($q->communeLabel ?? '') ?></td>
                        <td class="px-4 py-2 text-center">
                            <?php if ($isSuper || $isAdmin): ?>
                                <div class="inline-flex gap-2">
                                    <!-- Modifier : admin (2) et super (1) -->
                                    <a href="index.php?controller=quartier&action=edit&id=<?= $q->id ?>"
                                       class="w-8 h-8 flex items-center justify-center rounded-md bg-amber-100 text-amber-700 hover:bg-amber-200">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <!-- Supprimer : seulement super (1) -->
                                    <?php if ($isSuper): ?>
                                        <a href="index.php?controller=quartier&action=delete&id=<?= $q->id ?>"
                                           onclick="return confirm('Supprimer ce quartier ?');"
                                           class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-700 hover:bg-red-200">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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
    $('#quartierTable').DataTable({
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
