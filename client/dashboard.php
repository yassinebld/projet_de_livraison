<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_client.php");
    exit();
}

$client_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT nom, prenom, email, ville FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    header("Location: ../login/login_client.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord client</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header img {
            height: 60px;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        nav {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        nav a {
            padding: 10px 20px;
            background-color: #0055a5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #0078d7;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 30px;
            color: #003366;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        @media screen and (max-width: 600px) {
            nav {
                flex-direction: column;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <header>
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
        <h1>Bienvenue <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></h1>
    </header>

    <div class="container">
        <nav>
            <a href="passer_commande.php">📦 Passer une commande</a>
            <a href="suivi.php">📈 Suivi de mes commandes</a>
            <a href="changer_mot_de_passe.php">🔑 Changer mot de passe</a>
            <a href="../logout.php">🔒 Déconnexion</a>
        </nav>

        <h2>📋 Vos dernières commandes</h2>

        <?php
        $stmt2 = $conn->prepare("
            SELECT code, poids, prix, etat, date_creation 
            FROM commandes 
            WHERE client_id = ? 
            ORDER BY date_creation DESC 
            LIMIT 5
        ");
        $stmt2->bind_param("i", $client_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Poids</th>
                    <th>Prix</th>
                    <th>État</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result2->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['code']) ?></td>
                    <td><?= htmlspecialchars($row['poids']) ?> kg</td>
                    <td><?= htmlspecialchars($row['prix']) ?> DH</td>
                    <td>
                        <?php
                            $etat = htmlspecialchars($row['etat']);
                            echo match ($etat) {
                                'pris' => '📦 En cours de livraison',
                                'livré' => '✅ Livrée',
                                'non pris (client introuvable)' => '❌ Non prise - Client introuvable',
                                'non livré (destinataire introuvable)' => '❌ Non livrée - Destinataire introuvable',
                                'retourné au client' => '↩️ Retournée au client',
                                'en attente', '', null => '🕒 En attente de prise en charge',
                                default => ucfirst($etat)
                            };
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['date_creation']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Aucune commande récente.</p>
        <?php endif; ?>
    </div>
</body>
</html>
