<?php
session_start();
require_once('../config/db.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $conn->prepare("SELECT mot_de_passe FROM super_admin WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        if (password_verify($mot_de_passe, $hash)) {
            // Stockage simple de l'ID
            $_SESSION['super_admin'] = $id;
            header('Location: ../super_admin/dashboard.php');
            exit();
        } else {
            $message = "❌ Mot de passe incorrect.";
        }
    } else {
        $message = "❌ Identifiant introuvable.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Connexion Super Admin</title>
<style>
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        padding: 0;
        background-image: url('../images/barid_poste_autre.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #333;
    }

    .login-container {
        background: #fff;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        max-width: 380px;
        width: 100%;
        text-align: center;
    }
    .login-container img.logo {
        width: 110px;
        margin-bottom: 25px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }
    h2 {
        margin-bottom: 25px;
        color: #005bbb;
        font-weight: 700;
        font-size: 1.9rem;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    input[type="text"],
    input[type="password"] {
        padding: 14px 18px;
        border: 1.8px solid #ccc;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #003f7f;
        box-shadow: 0 0 8px rgba(0,63,127,0.4);
    }
    button {
        padding: 14px;
        background-color: #005bbb;
        color: white;
        font-size: 1.1rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 700;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #003f7f;
    }
    .message {
        margin-bottom: 18px;
        color: #d32f2f;
        font-weight: 700;
        background-color: #ffbaba;
        padding: 12px;
        border-radius: 10px;
    }
    @media (max-width: 420px) {
        .login-container {
            padding: 30px 20px;
            max-width: 90vw;
        }
    }
</style>
</head>
<body>
    <div class="login-container">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Barid Poste" class="logo" />
        <h2>Connexion Super Admin</h2>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="id" placeholder="Identifiant" required autofocus>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
            <p><a href="../index.php">Retour à l'accueil</a></p>
        </form>
    </div>
</body>
</html>
