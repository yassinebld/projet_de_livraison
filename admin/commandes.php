<?php
session_start();
require_once('../config/db.php');

// Gestion messages d'erreur/succès
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Vérification session
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

// Colonnes autorisées pour le tri
$sort_columns = ['ville_destinataire', 'date_creation', 'code'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'date_creation';
$order = (strtoupper($_GET['order'] ?? '') === 'ASC') ? 'ASC' : 'DESC';

$ville_filter = trim($_GET['ville'] ?? '');
$assign_filter = $_GET['assign'] ?? '';
$jour = $_GET['jour'] ?? '';
$mois = $_GET['mois'] ?? '';
$annee = $_GET['annee'] ?? '';

// Requête SQL principale
$sql = "
    SELECT commandes.*, 
           clients.nom AS client_nom, clients.prenom AS client_prenom, 
           livreurs.id AS livreur_id_affiche
    FROM commandes
    LEFT JOIN clients ON commandes.client_id = clients.id
    LEFT JOIN livreurs ON commandes.livreur_id = livreurs.id
    WHERE commandes.is_active = TRUE"; // FILTRE pour ne prendre que les commandes actives

$params = [];
$types = "";

// Filtres
if ($ville_filter !== '') {
    $sql .= " AND ville_destinataire = ?";
    $params[] = $ville_filter;
    $types .= "s";
}
if ($jour !== '') {
    $sql .= " AND DAY(date_creation) = ?";
    $params[] = intval($jour);
    $types .= "i";
}
if ($mois !== '') {
    $sql .= " AND MONTH(date_creation) = ?";
    $params[] = intval($mois);
    $types .= "i";
}
if ($annee !== '') {
    $sql .= " AND YEAR(date_creation) = ?";
    $params[] = intval($annee);
    $types .= "i";
}
if ($assign_filter === 'assigned') {
    $sql .= " AND livreur_id IS NOT NULL";
} elseif ($assign_filter === 'not_assigned') {
    $sql .= " AND livreur_id IS NULL";
}

$sql .= " ORDER BY $sort $order";

// Exécution de la requête
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$commandes = $stmt->get_result();
$total = $commandes->num_rows;

// Liste des villes
$all_villes = [];
$res = $conn->query("SELECT DISTINCT ville_destinataire FROM commandes WHERE is_active = TRUE ORDER BY ville_destinataire");
while ($row = $res->fetch_assoc()) {
    if (!empty($row['ville_destinataire'])) $all_villes[] = $row['ville_destinataire'];
}

// Fonction de tri dynamique
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
    <title>📦 Commandes - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f6fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .header img {
            height: 60px;
        }
        .header h1 {
            color: #003366;
            font-size: 24px;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #0055cc;
            font-weight: 600;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .filter-form {
            margin-bottom: 20px;
            background: #eef4fb;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filter-form select,
        .filter-form input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #e0e0e0;
            text-align: center;
        }
        th {
            background: #e6f0ff;
        }
        .action-link {
            color: #0055cc;
            font-weight: bold;
            text-decoration: none;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .stats {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .error {
            background-color: #f8d7da;
            color: #842029;
        }
        .success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>📦 Gestion des Commandes</h1>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
    </div>

    <nav>
        <a href="dashboard.php">⬅ Retour</a>
        <a href="../logout.php">🚪 Déconnexion</a>
    </nav>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="get" class="filter-form">
        <label>Ville :</label>
        <select name="ville">
            <option value="">-- Toutes --</option>
            <?php foreach ($all_villes as $ville): ?>
                <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $ville_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ville) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Jour :</label>
        <input type="number" name="jour" min="1" max="31" value="<?= htmlspecialchars($jour) ?>">

        <label>Mois :</label>
        <input type="number" name="mois" min="1" max="12" value="<?= htmlspecialchars($mois) ?>">

        <label>Année :</label>
        <input type="number" name="annee" min="2000" max="2100" value="<?= htmlspecialchars($annee) ?>">

        <label>Assignation :</label>
        <select name="assign">
            <option value="" <?= $assign_filter === '' ? 'selected' : '' ?>>-- Toutes --</option>
            <option value="assigned" <?= $assign_filter === 'assigned' ? 'selected' : '' ?>>Assignées</option>
            <option value="not_assigned" <?= $assign_filter === 'not_assigned' ? 'selected' : '' ?>>Non assignées</option>
        </select>

        <button type="submit">🔍 Filtrer</button>
        <a href="commandes.php" style="color:red;">Réinitialiser</a>
    </form>

    <div class="stats">📊 Nombre total de commandes : <?= $total ?></div>

    <table>
        <thead>
            <tr>
                <th><?= tri_lien('code', 'Code', $sort, $order) ?></th>
                <th>Client</th>
                <th>Poids</th>
                <th>Prix</th>
                <th>Téléphone</th>
                <th><?= tri_lien('ville_destinataire', 'Ville Dest.', $sort, $order) ?></th>
                <th>État</th>
                <th><?= tri_lien('date_creation', 'Date', $sort, $order) ?></th>
                <th>Assigné à (ID)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cmd = $commandes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($cmd['code']) ?></td>
                    <td><?= htmlspecialchars($cmd['client_nom'] . ' ' . $cmd['client_prenom']) ?></td>
                    <td><?= htmlspecialchars($cmd['poids']) ?> kg</td>
                    <td><?= htmlspecialchars($cmd['prix']) ?> MAD</td>
                    <td><?= htmlspecialchars($cmd['numero_destinataire']) ?></td>
                    <td><?= htmlspecialchars($cmd['ville_destinataire']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($cmd['etat'])) ?></td>
                    <td><?= htmlspecialchars($cmd['date_creation']) ?></td>
                    <td>
                        <?= $cmd['livreur_id_affiche']
                            ? htmlspecialchars($cmd['livreur_id_affiche'])
                            : '<em>Non assignée</em>' ?>
                    </td>
                    <td>
                        <?php if ($cmd['etat'] === 'en attente'): ?>
                            <a class="action-link" href="assign_commande.php?code=<?= urlencode($cmd['code']) ?>">Assigner</a> |
                            <a class="action-link" href="delete_commande.php?code=<?= urlencode($cmd['code']) ?>" onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                        <?php else: ?>
                            <em>Déjà <?= htmlspecialchars($cmd['etat']) ?></em> |
                            <span style="color:gray;cursor:not-allowed;" title="Suppression non autorisée">Supprimer</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>
</body>
</html>
