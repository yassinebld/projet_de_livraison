<?php
session_start();
require_once('../config/db.php');

// Vérifie si admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$livreur_id = intval($_GET['id']);
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
        $stmt = $conn->prepare("UPDATE livreurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $livreur_id);

        if ($stmt->execute()) {
            $success = "Mot de passe mis à jour avec succès.";
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
        $stmt->close();
    }
}

// Récupération info livreur
$stmt = $conn->prepare("SELECT nom, prenom FROM livreurs WHERE id = ?");
$stmt->bind_param("i", $livreur_id);
$stmt->execute();
$result = $stmt->get_result();
$livreur = $result->fetch_assoc();
$stmt->close();

if (!$livreur) {
    die("Livreur introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Changer Mot de Passe Livreur</title>
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
        align-items: flex-start;
        padding-top: 40px;
        color: #333;
    }
    .form-box {
        background: #fff;
        padding: 30px 35px 40px;
        max-width: 420px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        text-align: center;
        position: relative;
    }
    .logo {
        max-width: 160px;
        margin: 0 auto 25px;
        display: block;
    }
    h2 {
        margin-bottom: 25px;
        color: #003366;
        font-weight: 700;
        line-height: 1.3;
    }
    label {
        display: block;
        font-weight: 600;
        margin: 15px 0 8px;
        color: #0071bc;
        text-align: left;
    }
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
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
        margin-top: 30px;
        width: 100%;
        background-color: #0055aa;
        color: white;
        border: none;
        padding: 15px 0;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    input[type="submit"]:hover {
        background-color: #003f7d;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1.5px solid #c3e6cb;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 700;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1.5px solid #f5c6cb;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 700;
    }
    a {
        display: block;
        margin-top: 25px;
        text-align: center;
        color: #0071bc;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        transition: color 0.3s ease;
    }
    a:hover {
        color: #004a65;
        text-decoration: underline;
    }
    @media (max-width: 480px) {
        body {
            padding: 20px 10px;
            align-items: center;
        }
        .form-box {
            padding: 25px 20px 30px;
            max-width: 100%;
        }
        .logo {
            max-width: 140px;
            margin-bottom: 20px;
        }
    }
</style>
</head>
<body>

<div class="form-box">
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Groupe Poste Maroc" class="logo">

    <h2>Changer mot de passe de<br><?= htmlspecialchars($livreur['nom'] . ' ' . $livreur['prenom']) ?></h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <input type="submit" value="Mettre à jour">
    </form>

    <a href="livreurs.php">← Retour à la liste</a>
</div>

</body>
</html>
