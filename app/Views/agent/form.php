<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$mode     = $mode ?? 'create';
$agent    = $agent ?? null;
$fonctions = $fonctions ?? [];

$isEdit = ($mode === 'edit');

function field($name, $agent) {
    return $agent ? $agent->$name : '';
}
?>

<div class="p-4 sm:p-6 max-w-3xl mx-auto">

    <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-user-gear text-sky-600"></i>
        <span><?= $isEdit ? 'Modifier un agent' : 'Ajouter un agent' ?></span>
    </h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6">
        <form action="" method="post" enctype="multipart/form-data" class="space-y-5">

            <!-- Photo -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 border-b border-slate-100 pb-4 mb-2">
                <div class="flex items-center gap-3">
                    <?php if ($isEdit && $agent && $agent->photo): ?>
                        <img src="public/<?= $agent->photo ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border border-slate-200">
                    <?php else: ?>
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Photo de profil</label>
                    <input type="file" name="photo" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 border border-dashed border-slate-300 rounded-lg py-2 px-3">
                    <p class="mt-1 text-[11px] text-slate-400">Optionnel. Formats image uniquement.</p>
                </div>
            </div>

            <!-- Identité -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
                    <input type="text" name="nom" required
                           value="<?= field('nom', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Postnom</label>
                    <input type="text" name="postnom" required
                           value="<?= field('postnom', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prénom</label>
                    <input type="text" name="prenom" required
                           value="<?= field('prenom', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <!-- Sexe & Téléphone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sexe</label>
                    <select name="sexe" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" required>
                        <option value="">Sélectionner</option>
                        <option value="M" <?= field('sexe', $agent) === 'M' ? 'selected' : '' ?>>Masculin</option>
                        <option value="F" <?= field('sexe', $agent) === 'F' ? 'selected' : '' ?>>Féminin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="telephone" required
                           value="<?= field('telephone', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <!-- Adresse & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                    <input type="text" name="adresse" required
                           value="<?= field('adresse', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email"
                           value="<?= field('email', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <!-- Pseudo & Mot de passe -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pseudo</label>
                    <input type="text" name="pseudo" required
                           value="<?= field('pseudo', $agent) ?>"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        <?= $isEdit ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe' ?>
                    </label>
                    <input type="password" name="pwd" <?= $isEdit ? '' : 'required' ?>
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <!-- Fonction -->
            <div class="grid gap-1">
                <label class="block text-xs font-medium text-slate-600 mb-1">Fonction</label>
                <select name="idFonction" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <option value="">Sélectionner</option>

                    <?php foreach ($fonctions as $f): ?>
                        <option value="<?= $f->id ?>"
                            <?= $agent && $agent->idFonction == $f->id ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($f->libelFonction) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="index.php?controller=agent&action=index"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-xs sm:text-sm hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
                <button class="inline-flex items-center gap-2 bg-sky-600 text-white px-4 py-2 rounded-lg text-xs sm:text-sm hover:bg-sky-700 shadow-sm">
                    <i class="fa-solid fa-save"></i>
                    <span><?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?></span>
                </button>
            </div>

        </form>
    </div>
</div>
