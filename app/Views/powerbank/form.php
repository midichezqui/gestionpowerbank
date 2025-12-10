<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mode   = $mode   ?? 'create';
$title  = $title  ?? ($mode === 'edit' ? 'Modifier un PowerBank' : 'Ajouter un PowerBank');
$isEdit = ($mode === 'edit');

// Construire l’URL du formulaire
if ($isEdit && isset($powerbank) && $powerbank->id) {
    $actionUrl = "index.php?controller=powerbank&action=edit&id=" . urlencode($powerbank->id);
} else {
    $actionUrl = "index.php?controller=powerbank&action=create";
}

// Fonction utilitaire pour récupérer la valeur d’un champ
function field_value($name, $old, $powerbank, $default = '')
{
    if (isset($old[$name])) {
        return $old[$name];
    }
    if ($powerbank && isset($powerbank->$name)) {
        return $powerbank->$name;
    }
    return $default;
}

$old        = $old        ?? [];
$powerbank  = $powerbank  ?? null;
$errors     = $errors     ?? [];
$typesCable = $typesCable ?? [];
$statuts    = $statuts    ?? [];
$communes   = $communes   ?? [];

// Valeurs sélectionnées pour les selects (edit / old / sinon vide)
$selectedType   = isset($old['idTypeCable'])
    ? (string)$old['idTypeCable']
    : ($powerbank ? (string)$powerbank->idTypeCable : '');

$selectedStatut = isset($old['idStatut'])
    ? (string)$old['idStatut']
    : ($powerbank ? (string)$powerbank->idStatut : '');

$selectedCommune = isset($old['idCommune'])
    ? (string)$old['idCommune']
    : ($powerbank ? (string)$powerbank->idCommune : '');

?>

<div class="p-4 sm:p-6 max-w-2xl">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">
            <?= htmlspecialchars($title) ?>
        </h1>

        <a href="index.php?controller=powerbank&action=index"
           class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-600 hover:text-slate-900">
            <i class="fa-solid fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>

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

        <!-- Code PowerBank -->
        <div class="grid gap-1">
            <label for="codePower" class="text-xs font-medium text-slate-700">
                Code PowerBank <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="codePower"
                name="codePower"
                value="<?= htmlspecialchars(field_value('codePower', $old, $powerbank)) ?>"
                class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Ex: PB-001"
                required
            >
        </div>

        <!-- Date d’acquisition -->
        <div class="grid gap-1">
            <label for="dateAcquis" class="text-xs font-medium text-slate-700">
                Date d’acquisition <span class="text-red-500">*</span>
            </label>
            <input
                type="date"
                id="dateAcquis"
                name="dateAcquis"
                value="<?= htmlspecialchars(field_value('dateAcquis', $old, $powerbank)) ?>"
                class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                required
            >
        </div>

        <!-- Capacité -->
        <div class="grid gap-1">
            <label for="capacite" class="text-xs font-medium text-slate-700">
                Capacité <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="capacite"
                name="capacite"
                value="<?= htmlspecialchars(field_value('capacite', $old, $powerbank)) ?>"
                class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Ex: 10000 mAh"
                required
            >
        </div>

        <!-- Présentation écran -->
        <div class="grid gap-1">
            <label for="presentationEcran" class="text-xs font-medium text-slate-700">
                Présentation écran
            </label>
            <input
                type="text"
                id="presentationEcran"
                name="presentationEcran"
                value="<?= htmlspecialchars(field_value('presentationEcran', $old, $powerbank)) ?>"
                class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Ex: Affichage LED, LCD, Sans écran..."
            >
        </div>

 <!-- Type de câble, Statut et Commune (via SELECT dynamiques) -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Type de câble -->
    <div class="grid gap-1">
        <label for="idTypeCable" class="text-xs font-medium text-slate-700">
            Type de câble
        </label>
        <select
            id="idTypeCable"
            name="idTypeCable"
            class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
        >
            <option value="">Sélectionner un type</option>
            <?php foreach ($typesCable as $type) :
                $value = (string)$type['id'];
                $label = $type['libelType'] ?? ('Type #'.$value);
            ?>
                <option value="<?= htmlspecialchars($value) ?>"
                    <?= $value === $selectedType ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Statut -->
    <div class="grid gap-1">
        <label for="idStatut" class="text-xs font-medium text-slate-700">
            Statut
        </label>
        <select
            id="idStatut"
            name="idStatut"
            class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
        >
            <option value="">Sélectionner un statut</option>
            <?php foreach ($statuts as $st) :
                $value = (string)$st['id'];
                $label = $st['LibelStatut'] ?? ('Statut #'.$value);
            ?>
                <option value="<?= htmlspecialchars($value) ?>"
                    <?= $value === $selectedStatut ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Commune -->
    <div class="grid gap-1">
        <label for="idCommune" class="text-xs font-medium text-slate-700">
            Commune <span class="text-red-500">*</span>
        </label>
        <select
            id="idCommune"
            name="idCommune"
            class="px-3 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
        >
            <option value="">Sélectionner une commune</option>
            <?php foreach ($communes as $cm) :
                $value = (string)$cm['id'];
                $label = $cm['nomCommune'] ?? ('Commune #'.$value);
            ?>
                <option value="<?= htmlspecialchars($value) ?>"
                    <?= $value === $selectedCommune ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


        <!-- Tarif -->
        <div class="grid gap-1">
            <label for="tarif" class="text-xs font-medium text-slate-700">
                Tarif (par période) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="tarif"
                    name="tarif"
                    value="<?= htmlspecialchars(field_value('tarif', $old, $powerbank)) ?>"
                    class="w-full pl-3 pr-12 py-2 rounded-lg border text-sm border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Ex: 1000"
                    required
                >
                <span class="absolute inset-y-0 right-3 flex items-center text-xs text-slate-500">
                    CDF
                </span>
            </div>
        </div>

        <!-- Boutons -->
        <div class="pt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 shadow-sm">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
            </button>

            <a href="index.php?controller=powerbank&action=index"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200">
                <i class="fa-solid fa-xmark text-xs"></i>
                Annuler
            </a>
        </div>
    </form>
</div>
