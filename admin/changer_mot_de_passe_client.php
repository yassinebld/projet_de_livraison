<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

// Vérifier l’ID du client dans l’URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID client invalide.");
}

$client_id = intval($_GET['id']);
$success = $error = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($new_pass) || empty($confirm_pass)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE clients SET mot_de_passe = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $client_id])) {
            $success = "Mot de passe du client mis à jour.";
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}

// Récupérer infos client
$stmt = $conn->prepare("SELECT nom, prenom FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->get_result()->fetch_assoc();
if (!$client) {
    die("Client introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer Mot de Passe Client</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #005a87, #00a1d6);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        .container {
            background: #fff;
            padding: 35px 40px 45px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .logo {
            max-width: 180px;
            margin: 0 auto 25px;
            display: block;
        }
        h2 {
            color: #005a87;
            margin-bottom: 25px;
            font-weight: 700;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 6px;
            font-weight: 600;
            color: #0071bc;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 2px solid #0071bc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #00a1d6;
            box-shadow: 0 0 8px #00a1d6;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #0071bc;
            color: white;
            border: none;
            padding: 14px 0;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #005a87;
        }
        .success {
            color: #2e7d32;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .error {
            color: #d32f2f;
            margin-bottom: 20px;
            font-weight: 600;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #0071bc;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #004a65;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px 35px;
            }
            .logo {
                max-width: 140px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Logo en haut -->
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Groupe Poste Maroc" class="logo">

    <h2>Changer mot de passe de <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></h2>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <input type="submit" value="Mettre à jour">
    </form>

    <a href="clients.php">← Retour à la liste</a>
</div>

</body>
</html>
