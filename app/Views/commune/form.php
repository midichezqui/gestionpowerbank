<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$mode     = $mode ?? 'create';
$commune  = $commune ?? null;
$old      = $old ?? [];
$errors   = $errors ?? [];

$isEdit = ($mode === 'edit');

function field_value($name, $old, $commune)
{
    if (isset($old[$name])) return $old[$name];
    if ($commune) return $commune->$name;
    return '';
}

$actionUrl = $isEdit
    ? "index.php?controller=commune&action=edit&id=" . $commune->id
    : "index.php?controller=commune&action=create";
?>

<div class="p-4 sm:p-6 max-w-3xl mx-auto">

    <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-city text-sky-600"></i>
        <span><?= $isEdit ? 'Modifier une commune' : 'Ajouter une commune' ?></span>
    </h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6">

        <?php if (!empty($errors)) : ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form action="<?= $actionUrl ?>" method="post" class="space-y-4">

            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700 mb-1">Nom de la commune <span class="text-red-500">*</span></label>
                <input type="text"
                       name="nomCommune"
                       value="<?= htmlspecialchars(field_value('nomCommune', $old, $commune)) ?>"
                       class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4">
                <a href="index.php?controller=commune&action=index"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-xs sm:text-sm hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-sky-600 text-white px-4 py-2 rounded-lg text-xs sm:text-sm hover:bg-sky-700 shadow-sm">
                    <i class="fa-solid fa-save"></i>
                    <span><?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?></span>
                </button>
            </div>

        </form>
    </div>

</div>
