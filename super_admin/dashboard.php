<?php
session_start();

// Vérifier que le super admin est connecté
if (!isset($_SESSION['super_admin'])) {
    header('Location: ../login/login_super_admin.php');
    exit();
}

$id = $_SESSION['super_admin']; // Correction ici ✅
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Tableau de bord Super Admin</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 22px;
            margin: 0;
        }

        header img {
            height: 50px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        h2 {
            color: #003366;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 15px;
        }

        ul li a {
            display: inline-block;
            background-color: #0055a5;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        ul li a:hover {
            background-color: #0078d7;
        }

        @media screen and (max-width: 600px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            header h1 {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Bienvenue, Super Admin (<?= htmlspecialchars($id) ?>)</h1>
    <a class="logout-btn" href="../logout.php">🚪 Déconnexion</a>
</header>

<div class="container">
    <h2>📊 Tableau de bord Super Administrateur</h2>
    <ul>
        <li><a href="admins.php">👨‍💼 Gérer les Admins</a></li>
        <li><a href="livreurs.php">🚚 Gérer les Livreurs</a></li>
        <li><a href="commandes.php">📦 Gérer les Commandes</a></li>
        <li><a href="clients.php">🧍 Gérer les Clients</a></li>
        <li><a href="changer_mot_de_passe_superadmin.php">🔑 Changer Mot de Passe</a></li>
    </ul>
</div>

</body>
</html>
