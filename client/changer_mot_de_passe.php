<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_client.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

    if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
        $message = "❌ Veuillez remplir tous les champs.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $message = "❌ Le nouveau mot de passe ne correspond pas à la confirmation.";
    } else {
        $client_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT mot_de_passe FROM clients WHERE id = ?");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $stmt->bind_result($mdp_hash);

        if ($stmt->fetch()) {
            if (password_verify($ancien_mdp, $mdp_hash)) {
                $stmt->close();

                $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE clients SET mot_de_passe = ? WHERE id = ?");
                $update->bind_param("si", $nouveau_hash, $client_id);
                $update->execute();
                $update->close();

                $message = "✅ Mot de passe changé avec succès.";
            } else {
                $message = "❌ Ancien mot de passe incorrect.";
                $stmt->close();
            }
        } else {
            $message = "❌ Client introuvable.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 60px auto;
            background-color: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            height: 60px;
            margin-bottom: 10px;
        }

        h2 {
            color: #003366;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #0055a5;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0070cc;
        }

        .message {
            font-weight: bold;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #0055a5;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
        <h2>🔐 Changer votre mot de passe</h2>
    </div>

    <?php if ($message): ?>
        <div class="message <?= str_starts_with($message, '✅') ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Ancien mot de passe :</label>
        <input type="password" name="ancien_mdp" required>

        <label>Nouveau mot de passe :</label>
        <input type="password" name="nouveau_mdp" required>

        <label>Confirmer le nouveau mot de passe :</label>
        <input type="password" name="confirmer_mdp" required>

        <input type="submit" value="Changer le mot de passe">
    </form>

    <div class="back-link">
        <a href="dashboard.php">⬅ Retour au tableau de bord</a>
    </div>
</div>

</body>
</html>
