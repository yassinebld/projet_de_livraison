<?php
session_start();
require_once('../config/db.php'); // Connexion $conn

if (!isset($_SESSION['super_admin'])) {
    header('Location: ../login_super_admin.php');
    exit();
}

// Récupérer toutes les villes pour le filtre
$all_villes = [];
$res_villes = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom");
while ($row = $res_villes->fetch_assoc()) {
    $all_villes[] = $row['nom'];
}

// Colonnes triables
$sort_columns = ['id', 'ville', 'date_inscription'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'id';
$order = (strtoupper($_GET['order'] ?? '') === 'DESC') ? 'DESC' : 'ASC';

// Récupération des filtres
$cin_filter = trim($_GET['cin'] ?? '');
$telephone_filter = trim($_GET['telephone'] ?? '');
$ville_filter = $_GET['ville'] ?? '';
$date_jour = $_GET['date_jour'] ?? '';
$date_mois = $_GET['date_mois'] ?? '';
$date_annee = $_GET['date_annee'] ?? '';

// Construction de la requête SQL avec filtres dynamiques
$sql = "SELECT id, nom, prenom, ville, date_inscription, cin, telephone FROM clients WHERE 1=1";
$params = [];
$types = "";

// Filtre ville valide
if ($ville_filter !== '' && in_array($ville_filter, $all_villes)) {
    $sql .= " AND ville = ?";
    $params[] = $ville_filter;
    $types .= "s";
}

// Filtre CIN partiel
if ($cin_filter !== '') {
    $sql .= " AND cin LIKE ?";
    $params[] = "%$cin_filter%";
    $types .= "s";
}

// Filtre téléphone partiel
if ($telephone_filter !== '') {
    $sql .= " AND telephone LIKE ?";
    $params[] = "%$telephone_filter%";
    $types .= "s";
}

// Filtre date
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

// Ajout tri
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
    <meta charset="UTF-8" />
    <title>Super Admin - Liste des Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        header {
            margin-bottom: 2rem;
            text-align: center;
        }
        header img {
            max-height: 80px;
            margin-bottom: 0.5rem;
        }
        table th a {
            color: #fff;
            text-decoration: none;
        }
        table th a:hover {
            text-decoration: underline;
        }
        .btn-warning {
            background-color: #f0ad4e;
            border-color: #eea236;
            color: #fff;
        }
        .btn-warning:hover {
            background-color: #ec971f;
            border-color: #d58512;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <header>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Groupe Poste Maroc" />
        <h2>Liste des Clients</h2>
    </header>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-2">
            <label for="cin" class="form-label">CIN</label>
            <input type="text" id="cin" name="cin" class="form-control" placeholder="Recherche CIN" value="<?= htmlspecialchars($cin_filter) ?>" />
        </div>

        <div class="col-md-2">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Recherche Téléphone" value="<?= htmlspecialchars($telephone_filter) ?>" />
        </div>

        <div class="col-md-2">
            <label for="ville" class="form-label">Ville</label>
            <select id="ville" name="ville" class="form-select">
                <option value="">-- Toutes les villes --</option>
                <?php foreach ($all_villes as $v): ?>
                    <option value="<?= htmlspecialchars($v) ?>" <?= $v === $ville_filter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="date_jour" class="form-label">Filtrer par jour</label>
            <input type="date" id="date_jour" name="date_jour" class="form-control" value="<?= htmlspecialchars($date_jour) ?>" />
        </div>

        <div class="col-md-2">
            <label for="date_mois" class="form-label">Filtrer par mois</label>
            <input type="month" id="date_mois" name="date_mois" class="form-control" value="<?= htmlspecialchars($date_mois) ?>" />
        </div>

        <div class="col-md-2">
            <label for="date_annee" class="form-label">Filtrer par année</label>
            <input type="number" id="date_annee" name="date_annee" min="1900" max="<?= date('Y') ?>" class="form-control" value="<?= htmlspecialchars($date_annee) ?>" />
        </div>

        <div class="col-12 d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="clients.php" class="btn btn-secondary">Réinitialiser</a>
        </div>
    </form>

    <p><strong>Nombre de clients affichés :</strong> <?= $nb_clients ?></p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th><?= tri_lien('id', 'ID', $sort, $order) ?></th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>CIN</th>
                    <th>Téléphone</th>
                    <th><?= tri_lien('ville', 'Ville', $sort, $order) ?></th>
                    <th><?= tri_lien('date_inscription', 'Date Inscription', $sort, $order) ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($nb_clients === 0): ?>
                    <tr><td colspan="8">Aucun client trouvé.</td></tr>
                <?php else: ?>
                    <?php while ($client = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $client['id'] ?></td>
                            <td><?= htmlspecialchars($client['nom']) ?></td>
                            <td><?= htmlspecialchars($client['prenom']) ?></td>
                            <td><?= htmlspecialchars($client['cin']) ?></td>
                            <td><?= htmlspecialchars($client['telephone']) ?></td>
                            <td><?= htmlspecialchars($client['ville']) ?></td>
                            <td><?= htmlspecialchars($client['date_inscription']) ?></td>
                            <td>
                                <a href="changer_mot_de_passe_client.php?id=<?= $client['id'] ?>" class="btn btn-warning btn-sm">Changer mot de passe</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Retour au Tableau de Bord</a>
</div>

</body>
</html>
