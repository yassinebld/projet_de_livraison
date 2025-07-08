<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header img {
            height: 60px;
            margin-bottom: 15px;
        }

        h1 {
            color: #003366;
            margin-bottom: 30px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
        }

        nav ul li {
            margin: 12px 0;
        }

        nav ul li a {
            display: inline-block;
            width: 100%;
            max-width: 300px;
            padding: 12px 20px;
            background-color: #0055a5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #0070cc;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            nav ul li a {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
    </div>

    <h1>📋 Bienvenue Admin</h1>

    <nav>
        <ul>
            <li><a href="livreurs.php">🚚 Gérer les Livreurs</a></li>
            <li><a href="commandes.php">📦 Gérer les Commandes</a></li>
            <li><a href="clients.php">👤 Gérer les Clients</a></li>
            <li><a href="changer_mot_de_passe_admin.php">🔐 Changer Mot de Passe</a></li>
            <li><a href="../logout.php">🚪 Déconnexion</a></li>
        </ul>
    </nav>
</div>

</body>
</html>
