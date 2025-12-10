<?php
// Variables possibles : $title, $error, $old_pseudo
if (!isset($title)) {
    $title = 'Connexion agent';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN (pour démarrer rapidement) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <!-- Logo / Titre -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-500 shadow-lg mb-4">
                <span class="text-2xl font-extrabold text-white">PB</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Location PowerBank</h1>
            <p class="text-slate-300 text-sm mt-1">Espace sécurisé pour les agents</p>
        </div>

        <!-- Carte de connexion -->
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-xl p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 text-center">
                Connexion agent
            </h2>

            <!-- Message d'erreur -->
            <?php if (!empty($error)) : ?>
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="index.php?controller=auth&action=login" method="post" class="space-y-4">

                <div>
                    <label for="pseudo" class="block text-sm font-medium text-slate-700 mb-1">
                        Pseudo
                    </label>
                    <input
                        type="text"
                        id="pseudo"
                        name="pseudo"
                        value="<?= isset($old_pseudo) ? htmlspecialchars($old_pseudo) : '' ?>"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Entrez votre pseudo"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Mot de passe
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Entrez votre mot de passe"
                    >
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>Connectez vous avec vos identifiants agent</span>
                    <!-- Tu pourras ajouter ici un lien "Mot de passe oublié" plus tard -->
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-white transition"
                >
                    Se connecter
                </button>
            </form>
        </div>

        <!-- Pied de page -->
        <p class="mt-6 text-center text-xs text-slate-300">
            © <?= date('Y') ?> Location PowerBank. Tous droits réservés.
        </p>
    </div>

</body>
</html>
