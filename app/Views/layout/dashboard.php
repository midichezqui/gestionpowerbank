<?php
if (!isset($title)) {
    $title = 'Tableau de bord PowerBank';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Construire le nom complet de l'agent
$prenom   = $_SESSION['user_prenom']  ?? '';
$nom      = $_SESSION['user_nom']     ?? '';
$postnom  = $_SESSION['user_postnom'] ?? '';
$pseudo   = $_SESSION['user_pseudo']  ?? 'Agent';

$nomComplet = trim($prenom . ' ' . $nom . ' ' . $postnom);
if ($nomComplet === '') {
    $nomComplet = $pseudo;
}

// Rôle : seuls super (1) et admin (2) voient Rapports et Paramétrage
$userIdFonction = $_SESSION['user_idFonction'] ?? null;
$isAdminOrSuper = $userIdFonction !== null && in_array((int)$userIdFonction, [1, 2], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
            <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables core -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Responsive -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

</head>
<body class="min-h-screen bg-slate-100">

<div class="min-h-screen flex">

    <!-- Overlay mobile -->
    <div id="mobile-backdrop"
         class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"></div>

    <!-- Sidebar -->
    <aside id="mobile-sidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900/95 backdrop-blur text-slate-100 transform -translate-x-full transition-transform duration-200 ease-in-out
                  md:static md:translate-x-0 md:flex md:flex-col md:shadow-xl">

        <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3 justify-between md:justify-start">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-bolt text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold">PowerBank</h1>
                    <p class="text-xs text-slate-400">Gestion de location</p>
                </div>
            </div>

            <!-- Bouton de fermeture (mobile) -->
            <button id="closeSidebarBtn"
                    class="md:hidden text-slate-300 hover:text-white text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- MENU + PARAMÉTRAGE -->
         
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="index.php?controller=dashboard&action=index"
               class="flex items-center gap-3 px-3 py-2 rounded-lg bg-sky-600 text-white font-semibold shadow-sm">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>

            <a href="index.php?controller=location&action=index"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200/90 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="fa-solid fa-plug text-slate-300"></i>
                <span>Locations</span>
            </a>
            <a href="index.php?controller=affectation&action=index"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200/90 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="fa-solid fa-battery-full text-slate-300"></i>
                <span>Affectation</span>
            </a>

            <a href="index.php?controller=powerbank&action=index"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200/90 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="fa-solid fa-battery-full text-slate-300"></i>
                <span>PowerBanks</span>
            </a>

            <a href="index.php?controller=client&action=index"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-200/90 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="fa-solid fa-users text-slate-300"></i>
                <span>Clients</span>
            </a>
            
            <?php if ($isAdminOrSuper): ?>
                <!-- Sous-menu Rapports -->
                <div class="mt-3 border-t border-slate-800 pt-3">
                    <button
                        id="rapportsToggle"
                        type="button"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white text-left"
                    >
                        <span class="inline-flex items-center gap-3">
                            <i class="fa-solid fa-chart-column"></i>
                            <span>Rapports</span>
                        </span>
                        <i id="rapportsToggleIcon" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-150"></i>
                    </button>

                    <div id="rapportsMenu" class="mt-1 ml-9 space-y-1 text-xs text-slate-200 hidden">
                        <?php if ((int)$userIdFonction === 1): ?>
                            <a href="index.php?controller=rapport&action=performanceAgents"
                               class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                                Performance agents
                            </a>
                        <?php endif; ?>
                        <a href="index.php?controller=rapport&action=rapportParDate"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Rapport par date
                        </a>
                        <?php if ((int)$userIdFonction === 1): ?>
                            <a href="index.php?controller=rapport&action=chiffreAffaires"
                               class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                                Chiffre d'affaires
                            </a>
                        <?php endif; ?>
                        <a href="index.php?controller=rapport&action=rapportAffectationsJamaisLoue"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            PowerBanks jamais loués
                        </a>
                        <a href="index.php?controller=rapport&action=rapportHistoriqueNonLoue"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Historique PowerBanks jamais loués
                        </a>
                    </div>
                </div>

                <!-- Paramétrage avec sous-menu -->
                <div class="mt-3">
                    <button
                        id="paramToggle"
                        type="button"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 hover:text-white text-left"
                    >
                        <span class="inline-flex items-center gap-3">
                            <i class="fa-solid fa-gear"></i>
                            <span>Paramétrage</span>
                        </span>
                        <i id="paramToggleIcon" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-150"></i>
                    </button>

                    <div id="paramMenu" class="mt-1 ml-9 space-y-1 text-xs text-slate-200 hidden">
                        <a href="index.php?controller=agent&action=index"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Agent
                        </a>
                        <a href="index.php?controller=quartier&action=index"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Quartier
                        </a>
                        <a href="index.php?controller=commune&action=index"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Commune
                        </a>
                        <a href="index.php?controller=affectation&action=index"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Affectation
                        </a>
                        <a href="index.php?controller=fonction&action=index"
                           class="block px-2 py-1 rounded hover:bg-slate-800 hover:text-white">
                            Fonction
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </nav>

        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            <p class="mb-1">Connecté en tant que</p>
            <p class="font-semibold text-slate-100"><?= htmlspecialchars($nomComplet) ?></p>
            <a href="index.php?controller=auth&action=logout"
               class="inline-flex items-center gap-2 mt-3 text-red-300 hover:text-red-100 text-xs">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <main class="flex-1 flex flex-col md:ml-0">

        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Bouton menu (mobile) -->
                <button id="openSidebarBtn"
                        class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-800">
                        <?= htmlspecialchars($title) ?>
                    </h2>
                    <p class="text-[11px] sm:text-xs text-slate-500">
                        Vue générale des activités de location
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end text-xs">
                    <span class="text-slate-500">Bienvenue</span>
                    <span class="font-semibold text-slate-800"><?= htmlspecialchars($nomComplet) ?></span>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-sky-500 flex items-center justify-center text-white font-bold text-sm">
                    <?= strtoupper(substr($nomComplet, 0, 1)) ?>
                </div>
            </div>
        </header>

        <!-- 🧩 ICI on injecte le contenu de chaque page -->
        <section class="flex-1 p-4 sm:p-6 space-y-6">
            <?= $content ?>
        </section>
    </main>
</div>

<!-- Script pour sidebar + sous-menu -->
<script>
    const openBtn = document.getElementById('openSidebarBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const sidebar = document.getElementById('mobile-sidebar');
    const backdrop = document.getElementById('mobile-backdrop');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }

    if (openBtn)  openBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Sous-menu Paramétrage
    const paramToggle = document.getElementById('paramToggle');
    const paramMenu = document.getElementById('paramMenu');
    const paramToggleIcon = document.getElementById('paramToggleIcon');

    if (paramToggle && paramMenu && paramToggleIcon) {
        paramToggle.addEventListener('click', () => {
            const isHidden = paramMenu.classList.contains('hidden');
            if (isHidden) {
                paramMenu.classList.remove('hidden');
                paramToggleIcon.classList.add('rotate-180');
            } else {
                paramMenu.classList.add('hidden');
                paramToggleIcon.classList.remove('rotate-180');
            }
        });
    }

    // Sous-menu Rapports
    const rapportsToggle = document.getElementById('rapportsToggle');
    const rapportsMenu = document.getElementById('rapportsMenu');
    const rapportsToggleIcon = document.getElementById('rapportsToggleIcon');

    if (rapportsToggle && rapportsMenu && rapportsToggleIcon) {
        rapportsToggle.addEventListener('click', () => {
            const isHidden = rapportsMenu.classList.contains('hidden');
            if (isHidden) {
                rapportsMenu.classList.remove('hidden');
                rapportsToggleIcon.classList.add('rotate-180');
            } else {
                rapportsMenu.classList.add('hidden');
                rapportsToggleIcon.classList.remove('rotate-180');
            }
        });
    }
</script>

</body>
</html>
