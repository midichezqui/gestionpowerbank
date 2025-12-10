<?php
// core/Controller.php

class Controller
{
    /**
     * @param string $view   ex: 'powerbank/index'
     * @param array  $data   données envoyées à la vue
     * @param string $layout ex: 'dashboard' ou '' pour sans layout
     */
    protected function render(string $view, array $data = [], string $layout = '')
    {
        // Chaque clé du tableau devient une variable utilisable dans la vue
        extract($data);

        // Fichier de vue
        $viewFile = __DIR__ . '/../app/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("❌ Vue introuvable : $viewFile");
        }

        // Si pas de layout, on inclut juste la vue
        if ($layout === '' || $layout === null) {
            include $viewFile;
            return;
        }

        // Sinon on capture le rendu de la vue dans $content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Fichier de layout (ex: app/Views/layout/dashboard.php)
        $layoutFile = __DIR__ . '/../app/Views/layout/' . $layout . '.php';

        if (!file_exists($layoutFile)) {
            die("❌ Layout introuvable : $layoutFile");
        }

        // Le layout utilisera $content + les variables extraites plus haut
        include $layoutFile;
    }
}
