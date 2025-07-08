<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_client.php");
    exit();
}

$client_id = $_SESSION['user_id'];
$message = "";

// 🔧 Suppression de commande en attente
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $commande_code = trim($_GET['delete']);

    $verif = $conn->prepare("SELECT code FROM commandes WHERE code = ? AND client_id = ? AND etat = 'en attente'");
    $verif->bind_param("si", $commande_code, $client_id);
    $verif->execute();
    $verif_result = $verif->get_result();

    if ($verif_result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM commandes WHERE code = ?");
        $delete_stmt->bind_param("s", $commande_code);
        if ($delete_stmt->execute()) {
            $message = "✅ Commande supprimée avec succès.";
        } else {
            $message = "❌ Erreur lors de la suppression.";
        }
        $delete_stmt->close();
    } else {
        $message = "⚠️ Impossible de supprimer cette commande.";
    }
    $verif->close();
}

// 📦 Récupérer commandes avec livreur
$stmt = $conn->prepare("
    SELECT c.*, l.nom AS livreur_nom, l.prenom AS livreur_prenom
    FROM commandes c
    LEFT JOIN livreurs l ON c.livreur_id = l.id
    WHERE c.client_id = ?
    ORDER BY c.date_creation DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi des commandes</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            padding: 0;
            margin: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .header img {
            height: 60px;
        }
        .header h2 {
            color: #003366;
            margin: 0;
        }
        a {
            color: #0055a5;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .message {
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #003366;
            color: white;
        }
        tr:hover {
            background-color: #f0f8ff;
        }
        .delete-btn {
            color: #d10000;
            text-decoration: none;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            th {
                display: none;
            }
            td {
                position: relative;
                padding-left: 50%;
                text-align: left;
                border: none;
                border-bottom: 1px solid #ddd;
            }
            td::before {
                position: absolute;
                left: 10px;
                width: 45%;
                font-weight: bold;
            }
            td:nth-of-type(1)::before { content: "Code"; }
            td:nth-of-type(2)::before { content: "Destinataire"; }
            td:nth-of-type(3)::before { content: "Ville"; }
            td:nth-of-type(4)::before { content: "Poids"; }
            td:nth-of-type(5)::before { content: "Prix"; }
            td:nth-of-type(6)::before { content: "État"; }
            td:nth-of-type(7)::before { content: "Date"; }
            td:nth-of-type(8)::before { content: "Livreur"; }
            td:nth-of-type(9)::before { content: "Action"; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📈 Suivi de mes commandes</h2>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
    </div>

    <div class="nav-links">
        <a href="dashboard.php">🏠 Retour au tableau de bord</a> | 
        <a href="../logout.php">🔒 Déconnexion</a>
    </div>

    <?php if ($message): ?>
        <div class="message" style="color: <?= str_contains($message, '✅') ? 'green' : 'red' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Destinataire</th>
                <th>Ville</th>
                <th>Poids</th>
                <th>Prix</th>
                <th>État</th>
                <th>Date</th>
                <th>Livreur</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['code']) ?></td>
                        <td><?= htmlspecialchars($row['nom_destinataire'] . ' ' . $row['prenom_destinataire']) ?></td>
                        <td><?= htmlspecialchars($row['ville_destinataire']) ?></td>
                        <td><?= htmlspecialchars($row['poids']) ?> kg</td>
                        <td><?= htmlspecialchars($row['prix']) ?> DH</td>
                        <td>
                            <?php
                                echo match ($row['etat']) {
                                    'pris' => '📦 En cours',
                                    'livré' => '✅ Livrée',
                                    'non pris (client introuvable)' => '❌ Client introuvable',
                                    'non livré (destinataire introuvable)' => '❌ Destinataire introuvable',
                                    'retourné au client' => '↩️ Retournée',
                                    'en attente', '', null => '🕒 En attente',
                                    default => ucfirst($row['etat'])
                                };
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['date_creation']) ?></td>
                        <td>
                            <?= (!empty($row['livreur_nom']))
                                ? htmlspecialchars($row['livreur_nom'] . ' ' . $row['livreur_prenom'])
                                : "Non assigné" ?>
                        </td>
                        <td>
                            <?php if ($row['etat'] === 'en attente'): ?>
                                <a class="delete-btn" href="suivi.php?delete=<?= urlencode($row['code']) ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">Aucune commande trouvée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
