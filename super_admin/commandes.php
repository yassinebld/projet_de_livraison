<?php 
session_start();
require_once('../config/db.php');

// Vérification de la session
if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login_super_admin.php");
    exit();
}

// Tri et filtres
$sort_columns = ['code', 'date_creation', 'ville_destinataire', 'client_id', 'assignation'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'date_creation';
$order = (strtoupper($_GET['order'] ?? '') === 'ASC') ? 'ASC' : 'DESC';
$ville_filter = trim($_GET['ville'] ?? '');
$filter_jour = $_GET['jour'] ?? '';
$filter_mois = $_GET['mois'] ?? '';
$filter_annee = $_GET['annee'] ?? '';
$assignation = $_GET['assignation'] ?? '';

// Construction de la requête SQL
$sql = "
    SELECT commandes.*, 
           clients.nom AS client_nom, clients.prenom AS client_prenom, 
           livreurs.id AS livreur_id_affiche
    FROM commandes
    LEFT JOIN clients ON commandes.client_id = clients.id
    LEFT JOIN livreurs ON commandes.livreur_id = livreurs.id
    WHERE commandes.is_active = TRUE"; // Filtrer uniquement commandes actives

$params = [];
$types = "";

// Filtres ville
if ($ville_filter !== '') {
    $sql .= " AND ville_destinataire = ?";
    $params[] = $ville_filter;
    $types .= "s";
}

// Filtres date
if ($filter_jour !== '' && is_numeric($filter_jour) && (int)$filter_jour >= 1 && (int)$filter_jour <= 31) {
    $sql .= " AND DAY(date_creation) = ?";
    $params[] = $filter_jour;
    $types .= "i";
}
if ($filter_mois !== '' && is_numeric($filter_mois) && (int)$filter_mois >= 1 && (int)$filter_mois <= 12) {
    $sql .= " AND MONTH(date_creation) = ?";
    $params[] = $filter_mois;
    $types .= "i";
}
if ($filter_annee !== '' && is_numeric($filter_annee) && (int)$filter_annee >= 1900 && (int)$filter_annee <= 2100) {
    $sql .= " AND YEAR(date_creation) = ?";
    $params[] = $filter_annee;
    $types .= "i";
}

// Filtres assignation
if ($assignation === '1') {
    $sql .= " AND livreur_id IS NOT NULL";
} elseif ($assignation === '0') {
    $sql .= " AND livreur_id IS NULL";
}

$sql .= " ORDER BY $sort $order";

// Préparation et exécution
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$commandes = $stmt->get_result();

// Récupération des villes
$all_villes = [];
$res = $conn->query("SELECT DISTINCT ville_destinataire FROM commandes WHERE is_active = TRUE ORDER BY ville_destinataire");
while ($row = $res->fetch_assoc()) {
    if (!empty($row['ville_destinataire'])) {
        $all_villes[] = $row['ville_destinataire'];
    }
}

// Fonction pour créer les liens de tri
function tri_lien($col, $label, $current_sort, $current_order) {
    $order = ($current_sort === $col && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $arrow = ($current_sort === $col) ? ($current_order === 'ASC' ? ' ▲' : ' ▼') : '';
    $query = $_GET;
    $query['sort'] = $col;
    $query['order'] = $order;
    return "<a href=\"?" . http_build_query($query) . "\">{$label}{$arrow}</a>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo {
            height: 60px;
        }
        .filter-form select, .filter-form input {
            min-width: 120px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">📦 Gestion des Commandes</h2>
            <small class="text-muted">Total commandes affichées : <?= $commandes->num_rows ?></small>
        </div>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo" class="logo">
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-secondary me-2">← Tableau de bord</a>
        <a href="../logout.php" class="btn btn-danger">Déconnexion</a>
    </div>

    <form method="get" class="bg-white p-3 rounded shadow-sm mb-4 row g-3 align-items-end filter-form">
        <div class="col-md-3">
            <label for="ville" class="form-label">Ville destinataire :</label>
            <select name="ville" id="ville" class="form-select">
                <option value="">-- Toutes --</option>
                <?php foreach ($all_villes as $ville): ?>
                    <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $ville_filter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ville) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="jour" class="form-label">Jour :</label>
            <input type="number" min="1" max="31" name="jour" id="jour" class="form-control" value="<?= htmlspecialchars($filter_jour) ?>" placeholder="1-31">
        </div>
        <div class="col-md-2">
            <label for="mois" class="form-label">Mois :</label>
            <input type="number" min="1" max="12" name="mois" id="mois" class="form-control" value="<?= htmlspecialchars($filter_mois) ?>" placeholder="1-12">
        </div>
        <div class="col-md-3">
            <label for="annee" class="form-label">Année :</label>
            <input type="number" min="1900" max="2100" name="annee" id="annee" class="form-control" value="<?= htmlspecialchars($filter_annee) ?>" placeholder="ex: 2025">
        </div>

        <div class="col-md-2">
            <label for="assignation" class="form-label">Assignée :</label>
            <select name="assignation" id="assignation" class="form-select">
                <option value="">-- Toutes --</option>
                <option value="1" <?= $assignation === '1' ? 'selected' : '' ?>>Oui</option>
                <option value="0" <?= $assignation === '0' ? 'selected' : '' ?>>Non</option>
            </select>
        </div>

        <div class="col-md-12 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
            <a href="commandes.php" class="btn btn-outline-secondary">↺ Réinitialiser</a>
        </div>
    </form>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th><?= tri_lien('code', 'Code', $sort, $order) ?></th>
                    <th>ID Client</th>
                    <th>Client</th>
                    <th>Poids</th>
                    <th>Prix</th>
                    <th>Téléphone Dest.</th>
                    <th><?= tri_lien('ville_destinataire', 'Ville Dest.', $sort, $order) ?></th>
                    <th>État</th>
                    <th><?= tri_lien('date_creation', 'Date', $sort, $order) ?></th>
                    <th><?= tri_lien('assignation', 'Assignée', $sort, $order) ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($commandes && $commandes->num_rows > 0): ?>
                    <?php while ($cmd = $commandes->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($cmd['code']) ?></td>
                            <td><?= htmlspecialchars($cmd['client_id']) ?></td>
                            <td><?= htmlspecialchars($cmd['client_nom'] . ' ' . $cmd['client_prenom']) ?></td>
                            <td><?= htmlspecialchars($cmd['poids']) ?> kg</td>
                            <td><?= htmlspecialchars($cmd['prix']) ?> MAD</td>
                            <td><?= htmlspecialchars($cmd['numero_destinataire']) ?></td>
                            <td><?= htmlspecialchars($cmd['ville_destinataire']) ?></td>
                            <td>
                                <?php
                                    $etat = $cmd['etat'];
                                    $badgeClass = 'secondary';
                                    if ($etat === 'en attente') $badgeClass = 'warning';
                                    elseif ($etat === 'prise par livreur') $badgeClass = 'primary';
                                    elseif ($etat === 'livrée') $badgeClass = 'success';
                                    elseif ($etat === 'non prise (client introuvable)' || $etat === 'non livrée (destinataire introuvable)') $badgeClass = 'danger';
                                    elseif ($etat === 'retournée au client') $badgeClass = 'info';
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($etat)) ?></span>
                            </td>
                            <td><?= htmlspecialchars($cmd['date_creation']) ?></td>
                            <td>
                                <?= $cmd['livreur_id_affiche']
                                    ? htmlspecialchars($cmd['livreur_id_affiche'])
                                    : '<em class="text-muted">Non assignée</em>' ?>
                            </td>
                            <td>
                                <?php if ($cmd['etat'] === 'en attente'): ?>
                                    <a href="assign_commande.php?code=<?= urlencode($cmd['code']) ?>" class="btn btn-sm btn-outline-primary mb-1">Assigner</a>
                                    <a href="delete_commande.php?code=<?= urlencode($cmd['code']) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>Suppression interdite</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11" class="text-muted">Aucune commande trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
