<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

// Tri
$sort_columns = ['id', 'ville', 'cin', 'nom'];
$sort = in_array($_GET['sort'] ?? '', $sort_columns) ? $_GET['sort'] : 'nom';
$order = (strtoupper($_GET['order'] ?? '') === 'DESC') ? 'DESC' : 'ASC';

// Filtres
$filters = ["is_active = TRUE"];  // Filtrer uniquement les livreurs actifs
$params = [];
$types = "";

// ID
if (!empty($_GET['id'])) {
    $filters[] = "id = ?";
    $params[] = intval($_GET['id']);
    $types .= "i";
}

// Téléphone
if (!empty($_GET['telephone'])) {
    $filters[] = "telephone LIKE ?";
    $params[] = "%" . $_GET['telephone'] . "%";
    $types .= "s";
}

// CIN
if (!empty($_GET['cin'])) {
    $filters[] = "cin LIKE ?";
    $params[] = "%" . $_GET['cin'] . "%";
    $types .= "s";
}

// Ville
$ville_filter = trim($_GET['ville'] ?? '');
if ($ville_filter !== '') {
    $filters[] = "ville = ?";
    $params[] = $ville_filter;
    $types .= "s";
}

// Requête SQL
$sql = "SELECT * FROM livreurs";
if ($filters) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}
$sql .= " ORDER BY $sort $order";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erreur de préparation SQL : " . $conn->error);
}

// Bind uniquement si on a des paramètres
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$livreurs = $stmt->get_result();

// Récupération des villes des livreurs actifs
$all_villes = [];
$res = $conn->query("SELECT DISTINCT ville FROM livreurs WHERE is_active = TRUE ORDER BY ville");
while ($row = $res->fetch_assoc()) {
    if (!empty($row['ville'])) $all_villes[] = $row['ville'];
}

// Fonction tri
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
    <title>Gestion des Livreurs</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .header img {
            height: 60px;
        }

        .header h1 {
            color: #003366;
            font-size: 24px;
            margin: 0;
        }

        nav a {
            text-decoration: none;
            margin: 0 10px;
            color: #0055a5;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .filter-form {
            background: #eef2f7;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select,
        .filter-form button {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .filter-form a {
            color: red;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th a {
            color: inherit;
            text-decoration: none;
        }

        th a:hover {
            text-decoration: underline;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .actions a {
            display: inline-block;
            margin-right: 8px;
            color: #0055a5;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            tr {
                margin-bottom: 15px;
                background: #fff;
                padding: 10px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            }

            th, td {
                text-align: right;
                position: relative;
                padding-left: 50%;
            }

            th::before, td::before {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                font-weight: bold;
                white-space: nowrap;
            }

            td:nth-of-type(1)::before { content: "ID"; }
            td:nth-of-type(2)::before { content: "Nom"; }
            td:nth-of-type(3)::before { content: "Prénom"; }
            td:nth-of-type(4)::before { content: "Téléphone"; }
            td:nth-of-type(5)::before { content: "CIN"; }
            td:nth-of-type(6)::before { content: "Ville"; }
            td:nth-of-type(7)::before { content: "Actions"; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📋 Gestion des Livreurs</h1>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
    </div>

    <nav>
        <a href="dashboard.php">⬅ Retour</a>
        <a href="add_livreur.php">➕ Ajouter un Livreur</a>
        <a href="../logout.php">🚪 Déconnexion</a>
    </nav>

    <!-- Filtres -->
    <form method="get" class="filter-form">
        <input type="number" name="id" placeholder="ID" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
        <input type="text" name="telephone" placeholder="Téléphone" value="<?= htmlspecialchars($_GET['telephone'] ?? '') ?>">
        <input type="text" name="cin" placeholder="CIN" value="<?= htmlspecialchars($_GET['cin'] ?? '') ?>">
        <label for="ville">Ville :</label>
        <select name="ville" id="ville">
            <option value="">-- Toutes --</option>
            <?php foreach ($all_villes as $ville): ?>
                <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $ville_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ville) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔍 Filtrer</button>
        <a href="livreurs.php">Réinitialiser</a>
    </form>

    <!-- Tableau -->
    <table>
        <thead>
            <tr>
                <th><?= tri_lien('id', 'ID', $sort, $order) ?></th>
                <th><?= tri_lien('nom', 'Nom', $sort, $order) ?></th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th><?= tri_lien('cin', 'CIN', $sort, $order) ?></th>
                <th><?= tri_lien('ville', 'Ville', $sort, $order) ?></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($livreur = $livreurs->fetch_assoc()): ?>
                <tr>
                    <td><?= $livreur['id'] ?></td>
                    <td><?= htmlspecialchars($livreur['nom']) ?></td>
                    <td><?= htmlspecialchars($livreur['prenom']) ?></td>
                    <td><?= htmlspecialchars($livreur['telephone']) ?></td>
                    <td><?= htmlspecialchars($livreur['cin']) ?></td>
                    <td><?= htmlspecialchars($livreur['ville']) ?></td>
                    <td class="actions">
                        <a href="changer_mot_de_passe_livreur.php?id=<?= urlencode($livreur['id']) ?>">🔑 Mot de passe</a>
                        <a href="delete_livreur.php?id=<?= urlencode($livreur['id']) ?>" onclick="return confirm('Supprimer ce livreur ?')">🗑 Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
