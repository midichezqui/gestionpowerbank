<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../Models/Location.php';

class RapportController extends Controller
{
    public function performanceAgents()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Seul le rôle super (idFonction = 1) peut accéder à ce rapport
        $idFonction = $_SESSION['user_idFonction'] ?? null;
        if ((int)$idFonction !== 1) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }

        // Dates par défaut : aujourd’hui
        $start = $_GET['start'] ?? date('Y-m-d');
        $end   = $_GET['end']   ?? date('Y-m-d');

        // Récupérer rapport
        $stats = Location::performanceAgents($start, $end);

        $this->render('rapports/performance_agents', [
            'title' => "Performance des agents",
            'stats' => $stats,
            'start' => $start,
            'end'   => $end
        ], 'dashboard');
    }
    
    public function performanceAgentsCsv()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $start = $_GET['start'] ?? date('Y-m-d');
        $end   = $_GET['end']   ?? date('Y-m-d');

        $stats = Location::performanceAgents($start, $end);

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="performance_agents.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Agent',
            'Nombre de locations',
            'Chiffre (FC)',
            'Pénalités dues',
            'Pénalités payées',
            'Indice mauvais agent (%)',
        ], ';');

        foreach ($stats as $row) {
            $totalChiffre   = (float)($row['totalChiffre'] ?? 0);
            $totalPenalites = (int)($row['penalitesNonPayees'] ?? 0) + (int)($row['penalitesPayees'] ?? 0);
            $ratio          = $totalChiffre > 0 ? ($totalPenalites / $totalChiffre) * 100 : 0;

            fputcsv($output, [
                $row['agentNom'],
                (int)($row['totalLocations'] ?? 0),
                $totalChiffre,
                (int)($row['penalitesNonPayees'] ?? 0),
                (int)($row['penalitesPayees'] ?? 0),
                round($ratio, 2),
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function performanceAgentsPdf()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $start = $_GET['start'] ?? date('Y-m-d');
        $end   = $_GET['end']   ?? date('Y-m-d');

        $stats = Location::performanceAgents($start, $end);

        ob_start();
        ?>
        <h2 style="text-align:center;">Performance des agents du <?= htmlspecialchars($start) ?> au <?= htmlspecialchars($end) ?></h2>
        <table border="1" cellspacing="0" cellpadding="4" width="100%">
            <thead>
            <tr>
                <th>Agent</th>
                <th>Locations</th>
                <th>Chiffre (FC)</th>
                <th>Pénalités dues</th>
                <th>Pénalités payées</th>
                <th>Indice mauvais agent (%)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stats as $row): ?>
                <?php
                $totalChiffre   = (float)($row['totalChiffre'] ?? 0);
                $totalPenalites = (int)($row['penalitesNonPayees'] ?? 0) + (int)($row['penalitesPayees'] ?? 0);
                $ratio          = $totalChiffre > 0 ? ($totalPenalites / $totalChiffre) * 100 : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['agentNom']) ?></td>
                    <td><?= (int)($row['totalLocations'] ?? 0) ?></td>
                    <td><?= number_format($totalChiffre, 2) ?></td>
                    <td><?= (int)($row['penalitesNonPayees'] ?? 0) ?></td>
                    <td><?= (int)($row['penalitesPayees'] ?? 0) ?></td>
                    <td><?= number_format($ratio, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();

        if (ob_get_length()) {
            ob_clean();
        }

        require_once __DIR__ . '/../Libraries/dompdf/autoload.inc.php';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("performance_agents.pdf", ["Attachment" => true]);
        exit;
    }

    public function rapportParDate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $errors = [];
        $data   = [];

        $date1          = $_GET['date1'] ?? null;
        $date2          = $_GET['date2'] ?? null;
        $idAgent        = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
        $idQuartier     = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;
        $filtrePenalite = $_GET['filtrePenalite'] ?? '';

        // Listes pour les filtres
        $agents    = Location::getAllAgents();
        $quartiers = Location::getAllQuartiers();

        if ($date1 && $date2) {
            if ($date1 > $date2) {
                $errors[] = "La date de début ne peut pas être supérieure à la date de fin.";
            } else {
                $data = Location::getRapportParDate(
                    $date1,
                    $date2,
                    $idAgent,
                    $idQuartier,
                    $filtrePenalite
                );
            }
        }

        $this->render('rapports/rapport_par_date', [
            'title'          => 'Rapport de locations par date',
            'errors'         => $errors,
            'date1'          => $date1,
            'date2'          => $date2,
            'idAgent'        => $idAgent,
            'idQuartier'     => $idQuartier,
            'filtrePenalite' => $filtrePenalite,
            'agents'         => $agents,
            'quartiers'      => $quartiers,
            'data'           => $data,
        ], 'dashboard');
    }

        public function rapportParDateExcel()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $date1          = $_GET['date1'] ?? null;
    $date2          = $_GET['date2'] ?? null;
    $idAgent        = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
    $idQuartier     = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;
    $filtrePenalite = $_GET['filtrePenalite'] ?? '';

    if (!$date1 || !$date2) {
        die("Dates manquantes pour l'export.");
    }

    $rows = Location::getRapportParDate(
        $date1,
        $date2,
        $idAgent,
        $idQuartier,
        $filtrePenalite
    );

    // Important : s'assurer qu'aucun HTML/layout n'a été envoyé avant
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rapport_par_date.csv"');

    $output = fopen('php://output', 'w');

    // En-tête
    fputcsv($output, [
        'Date',
        'Client',
        'PowerBank',
        'Quartier',
        'Agent',
        'Montant',
        'Pénalité',
        'Statut pénalité',
    ], ';');

    // Lignes
    foreach ($rows as $r) {
        fputcsv($output, [
            $r['dateLocation'],
            $r['clientNom'],
            $r['powerCode'],
            $r['quartierNom'],
            $r['agentNom'],
            (float)($r['pt'] ?? 0),
            (float)($r['montantPenalitePaye'] ?? 0),
            $r['statutPenalite'] ?? '',
        ], ';');
    }

    fclose($output);
    exit;
}


    public function rapportParDatePdf()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $date1          = $_GET['date1'] ?? null;
    $date2          = $_GET['date2'] ?? null;
    $idAgent        = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
    $idQuartier     = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;
    $filtrePenalite = $_GET['filtrePenalite'] ?? '';

    if (!$date1 || !$date2) {
        die("Dates manquantes pour l'export PDF.");
    }

    $rows = Location::getRapportParDate(
        $date1,
        $date2,
        $idAgent,
        $idQuartier,
        $filtrePenalite
    );

    // Construire un petit HTML pour le PDF
    ob_start();
    ?>
    <h2 style="text-align:center;">Rapport de locations du <?= htmlspecialchars($date1) ?> au <?= htmlspecialchars($date2) ?></h2>
    <table border="1" cellspacing="0" cellpadding="4" width="100%">
        <thead>
        <tr>
            <th>Date</th>
            <th>Client</th>
            <th>PowerBank</th>
            <th>Quartier</th>
            <th>Agent</th>
            <th>Montant</th>
            <th>Pénalité</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['dateLocation']) ?></td>
                <td><?= htmlspecialchars($r['clientNom']) ?></td>
                <td><?= htmlspecialchars($r['powerCode']) ?></td>
                <td><?= htmlspecialchars($r['quartierNom']) ?></td>
                <td><?= htmlspecialchars($r['agentNom']) ?></td>
                <td><?= number_format((float)($r['pt'] ?? 0), 2) ?></td>
                <td><?= number_format((float)($r['montantPenalitePaye'] ?? 0), 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $html = ob_get_clean();

    // IMPORTANT : vider tout output avant headers PDF
    if (ob_get_length()) {
        ob_clean();
    }

    // Dompdf autoload
    require_once __DIR__ . '/../Libraries/dompdf/autoload.inc.php';

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("rapport_par_date.pdf", ["Attachment" => true]);
    exit;
}

public function chiffreAffaires()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Seul le rôle super (idFonction = 1) peut accéder à ce rapport
    $idFonction = $_SESSION['user_idFonction'] ?? null;
    if ((int)$idFonction !== 1) {
        header('Location: index.php?controller=dashboard&action=index');
        exit;
    }

    $errors = [];

    // Par défaut : depuis le début du mois jusqu’à aujourd’hui
    $date1 = $_GET['date1'] ?? date('Y-m-01');
    $date2 = $_GET['date2'] ?? date('Y-m-d');

    $rows   = [];
    $totals = [
        'nbLocations'       => 0,
        'totalCA'           => 0.0,
        'totalPenalite'     => 0.0,
        'totalSansPenalite' => 0.0,
    ];

    if ($date1 && $date2) {
        if ($date1 > $date2) {
            $errors[] = "La date de début ne peut pas être supérieure à la date de fin.";
        } else {
            $rows = Location::getChiffreAffaires($date1, $date2);

            foreach ($rows as $r) {
                $totals['nbLocations']       += (int)($r['nbLocations'] ?? 0);
                $totals['totalCA']           += (float)($r['totalCA'] ?? 0);
                $totals['totalPenalite']     += (float)($r['totalPenalite'] ?? 0);
                $totals['totalSansPenalite'] += (float)($r['totalSansPenalite'] ?? 0);
            }
        }
    }

    $this->render('rapports/chiffre_affaires', [
        'title'  => 'Chiffre d’affaires',
        'date1'  => $date1,
        'date2'  => $date2,
        'errors' => $errors,
        'rows'   => $rows,
        'totals' => $totals,
    ], 'dashboard');
}


    public function rapportAffectationsJamaisLoue()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $errors = [];

        // Par défaut, début du mois courant -> aujourd'hui
        $date1 = $_GET['date1'] ?? date('Y-m-01');
        $date2 = $_GET['date2'] ?? date('Y-m-d');

        $rows = [];

        if ($date1 && $date2) {
            if ($date1 > $date2) {
                $errors[] = "La date de début ne peut pas être supérieure à la date de fin.";
            } else {
                $rows = Location::getAffectationsJamaisLoue($date1, $date2);
            }
        }

        $this->render('rapports/affectations_jamais_loue', [
            'title'  => "PowerBanks affectés mais jamais loués",
            'rows'   => $rows,
            'date1'  => $date1,
            'date2'  => $date2,
            'errors' => $errors,
            'libere' => !empty($_GET['libere']),
        ], 'dashboard');
    }

    public function libererPowerbanksNonLoue()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $date1 = $_POST['date1'] ?? date('Y-m-d');
        $date2 = $_POST['date2'] ?? $date1;

        // Sécurité simple sur l'ordre des dates
        if ($date1 > $date2) {
            $tmp   = $date1;
            $date1 = $date2;
            $date2 = $tmp;
        }

        // Met à l'état libre tous les powerbanks concernés
        require_once __DIR__ . '/../Models/Powerbank.php';
        Powerbank::libererNonLoueParPeriode($date1, $date2);

        // Retour au rapport avec message de succès
        header('Location: index.php?controller=rapport&action=rapportAffectationsJamaisLoue&date1=' . urlencode($date1) . '&date2=' . urlencode($date2) . '&libere=1');
        exit;
    }

    public function rapportHistoriqueNonLoue()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $errors = [];

        // Par défaut, début du mois courant -> aujourd'hui
        $date1 = $_GET['date1'] ?? date('Y-m-01');
        $date2 = $_GET['date2'] ?? date('Y-m-d');

        $idAgent    = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
        $idQuartier = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;

        $rows = [];

        if ($date1 && $date2) {
            if ($date1 > $date2) {
                $errors[] = "La date de début ne peut pas être supérieure à la date de fin.";
            } else {
                $rows = Location::getAffectationsJamaisLoueParPeriode($date1, $date2, $idAgent, $idQuartier);
            }
        }

        $agents    = Location::getAllAgents();
        $quartiers = Location::getAllQuartiers();

        $this->render('rapports/affectations_jamais_loue_historique', [
            'title'  => "Historique des powerbanks affectés mais jamais loués",
            'rows'   => $rows,
            'date1'  => $date1,
            'date2'  => $date2,
            'errors' => $errors,
            'idAgent'    => $idAgent,
            'idQuartier' => $idQuartier,
            'agents'     => $agents,
            'quartiers'  => $quartiers,
        ], 'dashboard');
    }

    public function rapportHistoriqueNonLoueCsv()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $date1      = $_GET['date1'] ?? null;
        $date2      = $_GET['date2'] ?? null;
        $idAgent    = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
        $idQuartier = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;

        if (!$date1 || !$date2) {
            die("Dates manquantes pour l'export CSV.");
        }

        $rows = Location::getAffectationsJamaisLoueParPeriode($date1, $date2, $idAgent, $idQuartier);

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="historique_non_loue.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Date affectation',
            'PowerBank',
            'Agent',
            'Quartier',
        ], ';');

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['dateAffectation'],
                $row['powerCode'],
                $row['agentNom'],
                $row['quartierNom'],
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function rapportHistoriqueNonLouePdf()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $date1      = $_GET['date1'] ?? null;
        $date2      = $_GET['date2'] ?? null;
        $idAgent    = !empty($_GET['idAgent']) ? (int)$_GET['idAgent'] : null;
        $idQuartier = !empty($_GET['idQuartier']) ? (int)$_GET['idQuartier'] : null;

        if (!$date1 || !$date2) {
            die("Dates manquantes pour l'export PDF.");
        }

        $rows = Location::getAffectationsJamaisLoueParPeriode($date1, $date2, $idAgent, $idQuartier);

        ob_start();
        ?>
        <h2 style="text-align:center;">Historique des powerbanks affectés mais jamais loués du <?= htmlspecialchars($date1) ?> au <?= htmlspecialchars($date2) ?></h2>
        <table border="1" cellspacing="0" cellpadding="4" width="100%">
            <thead>
            <tr>
                <th>Date affectation</th>
                <th>PowerBank</th>
                <th>Agent</th>
                <th>Quartier</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['dateAffectation']) ?></td>
                    <td><?= htmlspecialchars($row['powerCode']) ?></td>
                    <td><?= htmlspecialchars($row['agentNom']) ?></td>
                    <td><?= htmlspecialchars($row['quartierNom']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();

        if (ob_get_length()) {
            ob_clean();
        }

        require_once __DIR__ . '/../Libraries/dompdf/autoload.inc.php';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("historique_non_loue.pdf", ["Attachment" => true]);
        exit;
    }

}
