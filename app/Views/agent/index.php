<?php
if (!isset($title)) $title = 'Liste des agents';
if (session_status() === PHP_SESSION_NONE) session_start();

$idFonction = $_SESSION['user_idFonction'] ?? null;
$isSuper = (int)$idFonction === 1;
$isAdmin = (int)$idFonction === 2;
?>

<div class="p-4 sm:p-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($title) ?></h1>
        <?php if ($isSuper || $isAdmin): ?>
            <a href="index.php?controller=agent&action=create"
               class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 text-sm flex items-center gap-2">
                <i class="fa fa-plus"></i> Ajouter
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white shadow-sm border rounded-xl p-3 overflow-x-auto">

        <table id="agentTable" class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b">
            <tr>
                <th class="px-4 py-2">Photo</th>
                <th class="px-4 py-2">Nom</th>
                <th class="px-4 py-2">Sexe</th>
                <th class="px-4 py-2">Téléphone</th>
                <th class="px-4 py-2">Fonction</th>
                <th class="px-8 py-4 text-center">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($agents as $a): ?>
                <tr class="border-b hover:bg-slate-50">
                    <td class="px-4 py-2">
                        <?php if ($a->photo): ?>
                            <img src="public/<?= htmlspecialchars($a->photo) ?>" class="w-10 h-10 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-slate-300"></div>
                        <?php endif ?>
                    </td>

                    <td class="px-4 py-2">
                        <?= htmlspecialchars($a->prenom . ' ' . $a->nom) ?>
                    </td>

                    <td class="px-4 py-2"><?= htmlspecialchars($a->sexe) ?></td>

                    <td class="px-4 py-2"><?= htmlspecialchars($a->telephone) ?></td>

                    <td class="px-4 py-2"><?= htmlspecialchars($a->fonctionLabel) ?></td>

                    <td class="px-8 py-4 text-center">
                        <?php if ($isSuper || $isAdmin): ?>
                            <a href="index.php?controller=agent&action=edit&id=<?= $a->id ?>"
                               class="w-8 h-8 bg-amber-100 text-amber-700 rounded-md flex items-center justify-center hover:bg-amber-200">
                                <i class="fa fa-pen"></i>
                            </a>

                            <?php if ($isSuper): ?>
                                <a href="index.php?controller=agent&action=delete&id=<?= $a->id ?>"
                                   onclick="return confirm('Supprimer cet agent ?')"
                                   class="w-8 h-8 bg-red-100 text-red-700 rounded-md flex items-center justify-center hover:bg-red-200 ml-2">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>

    </div>
</div>

<script>
$(document).ready(function () {
    $('#agentTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10
    });
});
</script>
