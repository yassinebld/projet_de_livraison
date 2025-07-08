<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

// --- Villes ---
$all_villes = [];
$res_villes = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom");
while ($row = $res_villes->fetch_assoc()) {
    $all_villes[] = $row['nom'];
}

// --- Tri ---
$sort_columns = ['id', 'ville', 'date_inscription', 'cin', 'telephone'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'id';
$order = (strtoupper($_GET['order'] ?? '') === 'DESC') ? 'DESC' : 'ASC';

// --- Filtres ---
$ville_filter = $_GET['ville'] ?? '';
$cin_filter = trim($_GET['cin'] ?? '');
$tel_filter = trim($_GET['telephone'] ?? '');
$id_filter = trim($_GET['id'] ?? '');
$date_jour = $_GET['date_jour'] ?? '';
$date_mois = $_GET['date_mois'] ?? '';
$date_annee = $_GET['date_annee'] ?? '';

// --- Requête SQL ---
$sql = "SELECT id, nom, prenom, email, telephone, cin, ville, date_inscription FROM clients WHERE 1=1";
$params = [];
$types = "";

if ($ville_filter !== '' && in_array($ville_filter, $all_villes)) {
    $sql .= " AND ville = ?";
    $params[] = $ville_filter;
    $types .= "s";
}
if (!empty($cin_filter)) {
    $sql .= " AND cin LIKE ?";
    $params[] = "%$cin_filter%";
    $types .= "s";
}
if (!empty($tel_filter)) {
    $sql .= " AND telephone LIKE ?";
    $params[] = "%$tel_filter%";
    $types .= "s";
}
if (!empty($id_filter) && is_numeric($id_filter)) {
    $sql .= " AND id = ?";
    $params[] = intval($id_filter);
    $types .= "i";
}
if (!empty($date_jour)) {
    $sql .= " AND DATE(date_inscription) = ?";
    $params[] = $date_jour;
    $types .= "s";
} elseif (!empty($date_mois)) {
    $sql .= " AND DATE_FORMAT(date_inscription, '%Y-%m') = ?";
    $params[] = $date_mois;
    $types .= "s";
} elseif (!empty($date_annee)) {
    $sql .= " AND YEAR(date_inscription) = ?";
    $params[] = $date_annee;
    $types .= "s";
}

$sql .= " ORDER BY $sort $order";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$nb_clients = $result->num_rows;

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
    <title>Liste des Clients</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f4f6fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #003366;
        }
        .header img {
            height: 60px;
        }
        nav a {
            text-decoration: none;
            margin-right: 15px;
            color: #007bff;
            font-weight: 600;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .filter-form {
            padding: 20px;
            background: #f0f4fa;
            border-radius: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-form label {
            font-weight: bold;
        }
        .filter-form input, .filter-form select, .filter-form button {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .count {
            font-weight: bold;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            text-align: center;
        }
        th {
            background-color: #eaf0fb;
        }
        th a {
            color: #003366;
            text-decoration: none;
        }
        th a:hover {
            text-decoration: underline;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>👥 Liste des Clients</h1>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
    </div>

    <nav>
        <a href="dashboard.php">⬅ Retour au tableau de bord</a>
    </nav>

    <form method="get" class="filter-form">
        <label for="ville">Ville :</label>
        <select name="ville" id="ville">
            <option value="">-- Toutes --</option>
            <?php foreach ($all_villes as $ville): ?>
                <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $ville_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ville) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>CIN :</label>
        <input type="text" name="cin" value="<?= htmlspecialchars($cin_filter) ?>">

        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($tel_filter) ?>">

        <label>ID :</label>
        <input type="number" name="id" value="<?= htmlspecialchars($id_filter) ?>">

        <label>Date (Jour) :</label>
        <input type="date" name="date_jour" value="<?= htmlspecialchars($date_jour) ?>">

        <label>Mois :</label>
        <input type="month" name="date_mois" value="<?= htmlspecialchars($date_mois) ?>">

        <label>Année :</label>
        <input type="number" name="date_annee" min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($date_annee) ?>">

        <button type="submit">🔍 Filtrer</button>
        <a href="clients.php">Réinitialiser</a>
    </form>

    <p class="count">Nombre de clients affichés : <?= $nb_clients ?></p>

    <table>
        <thead>
            <tr>
                <th><?= tri_lien('id', 'ID', $sort, $order) ?></th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th><?= tri_lien('telephone', 'Téléphone', $sort, $order) ?></th>
                <th><?= tri_lien('cin', 'CIN', $sort, $order) ?></th>
                <th><?= tri_lien('ville', 'Ville', $sort, $order) ?></th>
                <th><?= tri_lien('date_inscription', 'Date d\'inscription', $sort, $order) ?></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($nb_clients === 0): ?>
            <tr><td colspan="9">Aucun client trouvé.</td></tr>
        <?php else: ?>
            <?php while ($client = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $client['id'] ?></td>
                    <td><?= htmlspecialchars($client['nom']) ?></td>
                    <td><?= htmlspecialchars($client['prenom']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= htmlspecialchars($client['telephone']) ?></td>
                    <td><?= htmlspecialchars($client['cin']) ?></td>
                    <td><?= htmlspecialchars($client['ville']) ?></td>
                    <td><?= htmlspecialchars($client['date_inscription']) ?></td>
                    <td>
                        <a href="changer_mot_de_passe_client.php?id=<?= urlencode($client['id']) ?>">Changer mot de passe</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
