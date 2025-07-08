<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header('Location: ../login_super_admin.php');
    exit();
}

// 🔴 Désactivation d’un admin au lieu de suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    if ($id_to_delete != $_SESSION['super_admin']) {
        $delete_stmt = $conn->prepare("UPDATE admins SET is_active = FALSE WHERE id = ?");
        $delete_stmt->bind_param("i", $id_to_delete);
        $delete_stmt->execute();
    }
    header("Location: admins.php");
    exit();
}

// Filtres
$id_filter = $_GET['id'] ?? '';
$telephone_filter = $_GET['telephone'] ?? '';
$ville_filter = $_GET['ville'] ?? '';
$cin_filter = $_GET['cin'] ?? '';

// Construction de la requête
$sql = "SELECT * FROM admins WHERE 1=1 AND is_active = TRUE"; // Ajout de filtre is_active = TRUE
$params = [];
$types = "";

if ($id_filter !== '') {
    $sql .= " AND id = ?";
    $params[] = $id_filter;
    $types .= "i";
}
if ($telephone_filter !== '') {
    $sql .= " AND telephone LIKE ?";
    $params[] = "%$telephone_filter%";
    $types .= "s";
}
if ($ville_filter !== '') {
    $sql .= " AND ville = ?";
    $params[] = $ville_filter;
    $types .= "s";
}
if ($cin_filter !== '') {
    $sql .= " AND cin LIKE ?";
    $params[] = "%$cin_filter%";
    $types .= "s";
}

$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Récupération des villes
$villes = [];
$res_villes = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom ASC");
while ($row = $res_villes->fetch_assoc()) {
    $villes[] = $row['nom'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Liste des Admins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #005a87, #00a1d6);
            min-height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }
        .logo {
            max-width: 180px;
            display: block;
            margin: 0 auto 30px;
        }
        h2 {
            text-align: center;
            color: #005a87;
            margin-bottom: 35px;
        }
        .form-select, .form-control {
            border-radius: 8px;
        }
        .btn {
            border-radius: 8px;
            font-weight: 600;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm {
            padding: 6px 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Logo -->
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc" class="logo">

    <h2>Liste des Administrateurs</h2>

    <!-- 🔍 Formulaire de filtre -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="number" name="id" class="form-control" placeholder="ID" value="<?= htmlspecialchars($id_filter) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($telephone_filter) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="cin" class="form-control" placeholder="CIN" value="<?= htmlspecialchars($cin_filter) ?>">
        </div>
        <div class="col-md-3">
            <select name="ville" class="form-select">
                <option value="">-- Filtrer par ville --</option>
                <?php foreach ($villes as $ville): ?>
                    <option value="<?= htmlspecialchars($ville) ?>" <?= ($ville === $ville_filter) ? "selected" : "" ?>>
                        <?= htmlspecialchars($ville) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-50">Filtrer</button>
            <a href="admins.php" class="btn btn-outline-secondary w-50">Réinitialiser</a>
        </div>
    </form>

    <!-- 📋 Tableau des admins -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>CIN</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($admin = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $admin['id'] ?></td>
                            <td><?= htmlspecialchars($admin['nom']) ?></td>
                            <td><?= htmlspecialchars($admin['prenom']) ?></td>
                            <td><?= htmlspecialchars($admin['cin']) ?></td>
                            <td><?= htmlspecialchars($admin['telephone']) ?></td>
                            <td><?= htmlspecialchars($admin['ville']) ?></td>
                            <td>
                                <?php if ($admin['id'] != $_SESSION['super_admin']): ?>
                                    <a href="changer_mot_de_passe_admin.php?id=<?= $admin['id'] ?>" class="btn btn-warning btn-sm mb-1">
                                        Modifier mot de passe
                                    </a>
                                    <a href="admins.php?delete=<?= $admin['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?');">
                                        supprimer
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Super Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">Aucun administrateur trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ➕ Ajouter -->
    <div class="d-flex justify-content-between flex-wrap mt-4">
        <a href="ajouter_admin.php" class="btn btn-success">➕ Ajouter un administrateur</a>
        <a href="dashboard.php" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
</div>

</body>
</html>
