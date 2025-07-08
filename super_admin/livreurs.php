<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login/login_super_admin.php");
    exit();
}

$sort_columns = ['id', 'ville', 'cin', 'nom', 'telephone'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'nom';
$order = (strtoupper($_GET['order'] ?? '') === 'DESC') ? 'DESC' : 'ASC';

$id_filter = trim($_GET['id'] ?? '');
$cin_filter = trim($_GET['cin'] ?? '');
$ville_filter = trim($_GET['ville'] ?? '');
$telephone_filter = trim($_GET['telephone'] ?? '');

// Sélectionne uniquement les livreurs actifs
$sql = "SELECT * FROM livreurs WHERE is_active = TRUE";
$params = [];
$types = "";

if ($id_filter !== '') {
    $sql .= " AND id = ?";
    $params[] = $id_filter;
    $types .= "i";
}
if ($cin_filter !== '') {
    $sql .= " AND cin LIKE ?";
    $params[] = "%$cin_filter%";
    $types .= "s";
}
if ($ville_filter !== '') {
    $sql .= " AND ville = ?";
    $params[] = $ville_filter;
    $types .= "s";
}
if ($telephone_filter !== '') {
    $sql .= " AND telephone LIKE ?";
    $params[] = "%$telephone_filter%";
    $types .= "s";
}

$sql .= " ORDER BY $sort $order";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$livreurs = $stmt->get_result();

$all_villes = [];
$res = $conn->query("SELECT DISTINCT ville FROM livreurs WHERE ville IS NOT NULL AND ville != '' ORDER BY ville");
while ($row = $res->fetch_assoc()) {
    $all_villes[] = $row['ville'];
}

function tri_lien($col, $label, $current_sort, $current_order) {
    $order = ($current_sort === $col && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $arrow = ($current_sort === $col) ? ($current_order === 'ASC' ? ' ▲' : ' ▼') : '';
    $query = $_GET;
    $query['sort'] = $col;
    $query['order'] = $order;
    return "<a href=\"?" . http_build_query($query) . "\" class=\"text-white text-decoration-none\">{$label}{$arrow}</a>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Super Admin - Gestion des Livreurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .logo {
            height: 70px;
        }
        .header {
            text-align: center;
            padding: 20px;
        }
        .header h2 {
            margin-top: 10px;
            color: #003366;
        }
        .card {
            padding: 20px;
            border-radius: 10px;
        }
        .table th a {
            color: white;
            font-weight: bold;
        }
        .table th a:hover {
            text-decoration: underline;
        }
        .btn-sm {
            min-width: 90px;
        }
    </style>
</head>
<body>

<div class="container my-4">
    <div class="header">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo" class="logo">
        <h2>Gestion des Livreurs</h2>
    </div>

    <!-- 📌 Filtres -->
    <div class="card shadow-sm mb-4">
        <form method="get" class="row g-3">
            <div class="col-md-2">
                <input type="number" name="id" class="form-control" placeholder="ID" value="<?= htmlspecialchars($id_filter) ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="cin" class="form-control" placeholder="CIN" value="<?= htmlspecialchars($cin_filter) ?>">
            </div>
            <div class="col-md-3">
                <select name="ville" class="form-select">
                    <option value="">-- Toutes les villes --</option>
                    <?php foreach ($all_villes as $ville): ?>
                        <option value="<?= htmlspecialchars($ville) ?>" <?= ($ville === $ville_filter) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ville) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($telephone_filter) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                <a href="livreurs.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- 📋 Tableau des livreurs -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th><?= tri_lien('id', 'ID', $sort, $order) ?></th>
                    <th><?= tri_lien('nom', 'Nom', $sort, $order) ?></th>
                    <th>Prénom</th>
                    <th><?= tri_lien('telephone', 'Téléphone', $sort, $order) ?></th>
                    <th><?= tri_lien('cin', 'CIN', $sort, $order) ?></th>
                    <th><?= tri_lien('ville', 'Ville', $sort, $order) ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($livreurs->num_rows > 0): ?>
                    <?php while ($livreur = $livreurs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $livreur['id'] ?></td>
                            <td><?= htmlspecialchars($livreur['nom']) ?></td>
                            <td><?= htmlspecialchars($livreur['prenom']) ?></td>
                            <td><?= htmlspecialchars($livreur['telephone']) ?></td>
                            <td><?= htmlspecialchars($livreur['cin']) ?></td>
                            <td><?= htmlspecialchars($livreur['ville']) ?></td>
                            <td>
                                <a href="changer_mot_de_passe_livreur.php?id=<?= $livreur['id'] ?>" class="btn btn-warning btn-sm mb-1">🔒 Mot de passe</a>
                                <a href="delete_livreur.php?id=<?= $livreur['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce livreur ?')">🗑 Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">Aucun livreur trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between">
        <a href="add_livreur.php" class="btn btn-success">➕ Ajouter un Livreur</a>
        <a href="dashboard.php" class="btn btn-secondary">← Retour au Tableau de Bord</a>
    </div>
</div>

</body>
</html>
