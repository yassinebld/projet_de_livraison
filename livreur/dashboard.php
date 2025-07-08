<?php
session_start();
require_once "../config/db.php";

// Vérification session livreur
if (!isset($_SESSION['livreur']) || !isset($_SESSION['livreur']['id'])) {
    header("Location: ../login/login_livreur.php");
    exit();
}

$livreur_id = $_SESSION['livreur']['id'];

// Récupérer les commandes assignées
$stmt = $conn->prepare("
    SELECT commandes.*, clients.nom AS nom_client, clients.prenom AS prenom_client 
    FROM commandes 
    JOIN clients ON commandes.client_id = clients.id 
    WHERE commandes.livreur_id = ?
    ORDER BY commandes.date_creation DESC
");
$stmt->bind_param("i", $livreur_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Tableau de bord Livreur</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header img {
            height: 50px;
        }

        header h2 {
            margin: 0;
            font-size: 22px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        nav {
            margin-bottom: 20px;
        }

        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #0055a5;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        form {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        select, button {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .status {
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h2>📦 Bienvenue, <?= htmlspecialchars($_SESSION['livreur']['nom']) ?></h2>
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
</header>

<div class="container">
    <nav>
        <a href="changer_mot_de_passe.php">🔑 Changer mot de passe</a>
        <a href="../logout.php">🚪 Déconnexion</a>
    </nav>

    <h3>📋 Commandes assignées</h3>

    <?php if ($result->num_rows === 0): ?>
        <p>Aucune commande assignée pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Client</th>
                    <th>Ville</th>
                    <th>Adresse</th>
                    <th>Poids</th>
                    <th>Prix</th>
                    <th>État</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($commande = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($commande['code']) ?></td>
                    <td><?= htmlspecialchars($commande['nom_client'] . ' ' . $commande['prenom_client']) ?></td>
                    <td><?= htmlspecialchars($commande['ville_destinataire']) ?></td>
                    <td><?= htmlspecialchars($commande['adresse_destinataire']) ?></td>
                    <td><?= htmlspecialchars($commande['poids']) ?> kg</td>
                    <td><?= htmlspecialchars($commande['prix']) ?> DH</td>
                    <td class="status">
                        <?php
                            $etat = $commande['etat'];
                            echo match ($etat) {
                                'prise par livreur' => '📦 En cours',
                                'livrée' => '✅ Livrée',
                                'non prise (client introuvable)' => '❌ Client introuvable',
                                'non livrée (destinataire introuvable)' => '❌ Destinataire introuvable',
                                'retournée au client' => '↩️ Retournée',
                                default => '🕒 En attente',
                            };
                        ?>
                    </td>
                    <td>
                        <?php if ($etat === 'en attente'): ?>
                            <form method="post" action="maj_statut.php">
                                <input type="hidden" name="code_commande" value="<?= htmlspecialchars($commande['code']) ?>">
                                <select name="etat" required>
                                    <option value="">--Sélectionner--</option>
                                    <option value="prise par livreur">📦 Pris</option>
                                    <option value="non prise (client introuvable)">❌ Non pris</option>
                                </select>
                                <button type="submit">Valider</button>
                            </form>
                        <?php elseif ($etat === 'prise par livreur'): ?>
                            <form method="post" action="maj_statut.php">
                                <input type="hidden" name="code_commande" value="<?= htmlspecialchars($commande['code']) ?>">
                                <select name="etat" required>
                                    <option value="">--Sélectionner--</option>
                                    <option value="livrée">✅ Livrée</option>
                                    <option value="non livrée (destinataire introuvable)">❌ Non livrée</option>
                                    <option value="retournée au client">↩️ Retournée</option>
                                </select>
                                <button type="submit">Valider</button>
                            </form>
                        <?php else: ?>
                            <span>✔️ Traitement terminé</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
