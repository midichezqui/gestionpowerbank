<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mode      = $mode ?? 'create';
$title     = $title ?? ($mode === 'edit' ? 'Modifier un client' : 'Ajouter un client');
$isEdit    = ($mode === 'edit');

$client    = $client ?? null;
$old       = $old ?? [];
$errors    = $errors ?? [];
$quartiers = $quartiers ?? [];
$etats     = $etats ?? [];

// URL du form
if ($isEdit && $client && $client->id) {
    $actionUrl = "index.php?controller=client&action=edit&id=" . urlencode($client->id);
} else {
    $actionUrl = "index.php?controller=client&action=create";
}

// Helper valeurs
function client_field($name, $old, $client, $default = '')
{
    if (isset($old[$name])) {
        return $old[$name];
    }
    if ($client && isset($client->$name)) {
        return $client->$name;
    }
    return $default;
}

$selectedQuartier = isset($old['idQuartier'])
    ? (string)$old['idQuartier']
    : ($client ? (string)$client->idQuartier : '');

$selectedEtat = isset($old['idEtat'])
    ? (string)$old['idEtat']
    : ($client ? (string)$client->idEtat : '');
?>

<div class="p-4 sm:p-6 max-w-3xl mx-auto">

    <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-user-group text-sky-600"></i>
        <span><?= htmlspecialchars($title) ?></span>
    </h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6">

        <?php if (!empty($errors)) : ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $err) : ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($actionUrl) ?>" method="post" class="space-y-4">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="prenom">
                    Prénom <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="prenom"
                       name="prenom"
                       value="<?= htmlspecialchars(client_field('prenom', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>

            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="nom">
                    Nom <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="nom"
                       name="nom"
                       value="<?= htmlspecialchars(client_field('nom', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>

            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="postnom">
                    Postnom <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="postnom"
                       name="postnom"
                       value="<?= htmlspecialchars(client_field('postnom', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="sexe">
                    Sexe <span class="text-red-500">*</span>
                </label>
                <select id="sexe"
                        name="sexe"
                        class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        required>
                    <option value="">Sélectionner</option>
                    <?php
                    $curSexe = client_field('sexe', $old, $client);
                    ?>
                    <option value="M" <?= $curSexe === 'M' ? 'selected' : '' ?>>M</option>
                    <option value="F" <?= $curSexe === 'F' ? 'selected' : '' ?>>F</option>
                </select>
            </div>

            <div class="grid gap-1 sm:col-span-2">
                <label class="text-xs font-medium text-slate-700" for="adresse">
                    Adresse <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="adresse"
                       name="adresse"
                       value="<?= htmlspecialchars(client_field('adresse', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="idQuartier">
                    Quartier <span class="text-red-500">*</span>
                </label>
                <select id="idQuartier"
                        name="idQuartier"
                        class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        required>
                    <option value="">Sélectionner un quartier</option>
                    <?php foreach ($quartiers as $q): 
                        $value = (string)$q['id'];
                        $label = $q['nomQuartier'] ?? ('Quartier #' . $value);
                    ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                            <?= $value === $selectedQuartier ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="telephone">
                    Téléphone <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="telephone"
                       name="telephone"
                       value="<?= htmlspecialchars(client_field('telephone', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="personneContact">
                    Personne de contact
                </label>
                <input type="text"
                       id="personneContact"
                       name="personneContact"
                       value="<?= htmlspecialchars(client_field('personneContact', $old, $client)) ?>"
                       class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div class="grid gap-1">
                <label class="text-xs font-medium text-slate-700" for="idEtat">
                    État <span class="text-red-500">*</span>
                </label>
                <select id="idEtat"
                        name="idEtat"
                        class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        required>
                    <option value="">Sélectionner un état</option>
                    <?php foreach ($etats as $e): 
                        $value = (string)$e['id'];
                        $label = $e['libelEtat'] ?? ('État #' . $value);
                    ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                            <?= $value === $selectedEtat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-2 sm:gap-3 justify-end">
            <a href="index.php?controller=client&action=index"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Retour
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 shadow-sm">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
            </button>
        </div>
        </form>
    </div>
</div>
