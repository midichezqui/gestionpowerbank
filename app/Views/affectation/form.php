<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$mode        = $mode ?? 'create';
$isEdit      = ($mode === 'edit');

$agents      = $agents      ?? [];
$communes    = $communes    ?? [];
$affectation = $affectation ?? null;
$errors      = $errors      ?? [];
$old         = $old         ?? [];

$title = $title ?? ($isEdit ? 'Modifier une affectation' : 'Nouvelle affectation');

// Helpers
function old_value($name, $old, $affectation, $default = '')
{
    if (isset($old[$name])) return $old[$name];
    if ($affectation && isset($affectation->$name)) return $affectation->$name;
    return $default;
}

// Formattage valeur datetime-local
$dtValue = old_value('dateAffectation', $old, $affectation);
if ($dtValue && str_contains($dtValue, ' ')) {
    $dtValue = substr(str_replace(' ', 'T', $dtValue), 0, 16);
}
?>

<div class="p-4 sm:p-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-sky-100 text-sky-600">
                    <i class="fa-solid fa-route text-sm"></i>
                </span>
                <span><?= htmlspecialchars($title) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Planifiez les tournées de vos agents et affectez des PowerBanks par commune.
            </p>
        </div>

        <a href="index.php?controller=affectation&action=index"
           class="inline-flex items-center gap-2 text-xs sm:text-sm px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 shadow-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Retour à la liste
        </a>
    </div>

    <!-- Carte principale -->
    <div class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- Bandeau haut -->
        <div class="px-4 sm:px-6 py-3 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <i class="fa-solid fa-circle-info text-sky-500"></i>
                <span>
                    Un agent peut être affecté à plusieurs PowerBanks pour une même date.
                </span>
            </div>
        </div>

        <div class="px-4 sm:px-6 py-5">

            <!-- Zone erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <div>
                            <p class="font-semibold mb-1">Veuillez corriger les points suivants :</p>
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <?php if (!$isEdit): ?>
                <!-- MODE CREATE : Agent + Date + Commune + Lignes dynamiques -->

                <form action="" method="post" class="space-y-6">

                    <!-- Section Contexte -->
                    <section class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-xs">
                                    1
                                </span>
                                Informations générales
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            <!-- Agent -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">
                                    Agent <span class="text-red-500">*</span>
                                </label>
                                <select name="idAgent"
                                        class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                        required>
                                    <option value="">Sélectionner un agent</option>
                                    <?php
                                    $selectedAgent = $old['idAgent'] ?? '';
                                    foreach ($agents as $ag):
                                        $id   = (string)$ag['id'];
                                        $nomC = trim($ag['prenom'] . ' ' . $ag['nom']);
                                    ?>
                                        <option value="<?= htmlspecialchars($id) ?>"
                                            <?= $id === (string)$selectedAgent ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nomC) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <!-- Date -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">
                                    Date d'affectation <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="dateAffectation"
                                       readonly
                                       value="<?= htmlspecialchars($dtValue ?: date('Y-m-d\TH:i')) ?>"
                                       class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                       required>
                            </div>

                            <!-- Commune -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">
                                    Commune <span class="text-red-500">*</span>
                                </label>
                                <select name="idCommune"
                                        id="idCommune"
                                        class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                        required>
                                    <option value="">Sélectionner une commune</option>
                                    <?php
                                    $selectedCommune = $old['idCommune'] ?? '';
                                    foreach ($communes as $c):
                                        $value = (string)$c['id'];
                                    ?>
                                        <option value="<?= htmlspecialchars($value) ?>"
                                            <?= $value === (string)$selectedCommune ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nomCommune']) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Section liste des PowerBanks -->
                    <section class="space-y-3 border-t border-slate-100 pt-4 mt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                    2
                                </span>
                                PowerBanks de la commune sélectionnée
                            </h2>

                            <button type="button"
                                    id="toggleAllPowerbanks"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-check-double text-[10px]"></i>
                                <span>Tout cocher</span>
                            </button>
                        </div>

                        <p class="text-xs text-slate-500">
                            Cochez les PowerBanks que vous souhaitez affecter à cet agent pour cette date et cette commune.
                        </p>

                        <div id="powerbanksContainer" class="mt-2 space-y-2">
                            <p class="text-xs text-slate-400" id="powerbanksHint">
                                Sélectionnez d’abord une commune pour voir les PowerBanks disponibles.
                            </p>
                        </div>

                    </section>

                    <!-- Boutons -->
                    <div class="pt-4 border-t border-slate-100 mt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Enregistrer les affectations
                        </button>
                        <a href="index.php?controller=affectation&action=index"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            Annuler
                        </a>
                    </div>

                </form>

                <!-- JS mode CREATE -->
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const communeSelect       = document.getElementById('idCommune');
                    const container           = document.getElementById('powerbanksContainer');
                    const hint                = document.getElementById('powerbanksHint');
                    const toggleAllBtn        = document.getElementById('toggleAllPowerbanks');

                    function renderPowerbanks(list) {
                        container.innerHTML = '';

                        if (!list || list.length === 0) {
                            const p = document.createElement('p');
                            p.className = 'text-xs text-slate-400';
                            p.textContent = 'Aucun PowerBank disponible pour cette commune.';
                            container.appendChild(p);
                            return;
                        }

                        const grid = document.createElement('div');
                        grid.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2';

                        list.forEach(function (pb) {
                            const label = document.createElement('label');
                            label.className = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs';

                            const checkbox = document.createElement('input');
                            checkbox.type  = 'checkbox';
                            checkbox.name  = 'idPower[]';
                            checkbox.value = pb.id;

                            const span = document.createElement('span');
                            span.textContent = pb.codePower;

                            label.appendChild(checkbox);
                            label.appendChild(span);
                            grid.appendChild(label);
                        });

                        container.appendChild(grid);
                    }

                    function updateToggleAllLabel() {
                        if (!toggleAllBtn) return;
                        const checkboxes = container.querySelectorAll('input[type="checkbox"][name="idPower[]"]');
                        if (checkboxes.length === 0) {
                            toggleAllBtn.querySelector('span').textContent = 'Tout cocher';
                            return;
                        }
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        toggleAllBtn.querySelector('span').textContent = allChecked ? 'Tout décocher' : 'Tout cocher';
                    }

                    if (toggleAllBtn) {
                        toggleAllBtn.addEventListener('click', function () {
                            const checkboxes = container.querySelectorAll('input[type="checkbox"][name="idPower[]"]');
                            if (checkboxes.length === 0) return;

                            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                            const newState   = !allChecked;

                            checkboxes.forEach(cb => { cb.checked = newState; });
                            updateToggleAllLabel();
                        });
                    }

                    container.addEventListener('change', function (e) {
                        if (e.target && e.target.matches('input[type="checkbox"][name="idPower[]"]')) {
                            updateToggleAllLabel();
                        }
                    });

                    communeSelect.addEventListener('change', function () {
                        const idCommune = this.value;
                        container.innerHTML = '';

                        if (!idCommune) {
                            const p = document.createElement('p');
                            p.className = 'text-xs text-slate-400';
                            p.textContent = 'Sélectionnez d\'abord une commune pour voir les PowerBanks disponibles.';
                            container.appendChild(p);
                            updateToggleAllLabel();
                            return;
                        }

                        const loading = document.createElement('p');
                        loading.className = 'text-xs text-slate-400';
                        loading.textContent = 'Chargement des PowerBanks...';
                        container.appendChild(loading);

                        fetch('index.php?controller=affectation&action=powerbanksByCommune&idCommune=' + encodeURIComponent(idCommune))
                            .then(function (response) { return response.json(); })
                            .then(function (data) {
                                renderPowerbanks(data);
                                updateToggleAllLabel();
                            })
                            .catch(function () {
                                container.innerHTML = '';
                                const p = document.createElement('p');
                                p.className = 'text-xs text-red-500';
                                p.textContent = 'Erreur lors du chargement des PowerBanks.';
                                container.appendChild(p);
                                updateToggleAllLabel();
                            });
                    });

                    // Si une commune est déjà sélectionnée (retour avec erreurs), tenter de recharger
                    if (communeSelect.value) {
                        const event = new Event('change');
                        communeSelect.dispatchEvent(event);
                    }
                });
                </script>

            <?php else: ?>
                <!-- MODE EDIT : une seule affectation -->

                <form action="" method="post" class="space-y-6">

                    <section class="space-y-3">
                        <h2 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-xs">
                                1
                            </span>
                            Modifier l’affectation
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Agent -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">Agent</label>
                                <select name="idAgent"
                                        class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                        required>
                                    <option value="">Sélectionner un agent</option>
                                    <?php
                                    $currentAgent = old_value('idAgent', $old, $affectation, $affectation->idAgent);
                                    foreach ($agents as $ag):
                                        $id   = (string)$ag['id'];
                                        $nomC = trim($ag['prenom'] . ' ' . $ag['nom'] . ' ' . $ag['postnom']);
                                    ?>
                                        <option value="<?= htmlspecialchars($id) ?>"
                                            <?= $id === (string)$currentAgent ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nomC) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <!-- Date -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">Date d'affectation</label>
                                <input type="datetime-local"
                                       name="dateAffectation"
                                       value="<?= htmlspecialchars($dtValue) ?>"
                                       class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                       required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- PowerBank -->
                            <div class="grid gap-1">
                                <label class="text-xs font-medium text-slate-700">PowerBank</label>
                                <select name="idPower"
                                        class="px-3 py-2 border rounded-lg text-sm bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                        required>
                                    <option value="">Sélectionner</option>
                                    <?php
                                    $currentPower = old_value('idPower', $old, $affectation, $affectation->idPower);
                                    foreach ($powerbanks as $pb):
                                        $value = (string)$pb['id'];
                                    ?>
                                        <option value="<?= htmlspecialchars($value) ?>"
                                            <?= $value === (string)$currentPower ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pb['codePower']) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                    </section>

                    <div class="pt-4 border-t border-slate-100 mt-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Mettre à jour
                        </button>
                        <a href="index.php?controller=affectation&action=index"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            Annuler
                        </a>
                    </div>

                </form>

            <?php endif; ?>

        </div>
    </div>
</div>
