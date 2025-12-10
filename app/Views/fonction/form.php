<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mode     = $mode ?? 'create';
$fonction = $fonction ?? null;
$errors   = $errors ?? [];
$old      = $old ?? [];

$isEdit = ($mode === 'edit');

function f_field($name, $old, $fonction)
{
    if (isset($old[$name])) {
        return $old[$name];
    }
    if ($fonction && isset($fonction->$name)) {
        return $fonction->$name;
    }
    return '';
}

$actionUrl = $isEdit
    ? "index.php?controller=fonction&action=edit&id=" . $fonction->id
    : "index.php?controller=fonction&action=create";
?>

<div class="p-4 sm:p-6 max-w-xl">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">
            <?= $isEdit ? 'Modifier une fonction' : 'Ajouter une fonction' ?>
        </h1>

        <a href="index.php?controller=fonction&action=index"
           class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>

    <?php if (!empty($errors)) : ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $err) : ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($actionUrl) ?>" method="post" class="space-y-4">

        <div class="grid gap-1">
            <label for="libelFonction" class="text-xs font-medium text-slate-700">
                Libellé de la fonction <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="libelFonction"
                name="libelFonction"
                value="<?= htmlspecialchars(f_field('libelFonction', $old, $fonction)) ?>"
                class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Ex : Caissier, Gérant, Responsable Point"
                required
            >
        </div>

        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 shadow-sm">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
            </button>

            <a href="index.php?controller=fonction&action=index"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200">
                <i class="fa-solid fa-xmark text-xs"></i>
                Annuler
            </a>
        </div>

    </form>
</div>
