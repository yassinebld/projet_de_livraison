<?php
session_start();
require_once('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($code, $admin['mot_de_passe'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['nom'] = $admin['nom'];
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $erreur = "ID ou mot de passe incorrect.";
        }
    } else {
        $erreur = "ID ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Connexion Admin</title>
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
        background: rgba(255, 255, 255, 0.95);
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 6px 25px rgba(0,0,0,0.2);
        max-width: 380px;
        width: 100%;
        text-align: center;
    }
    .login-container img.logo {
        width: 100px;
        margin-bottom: 20px;
    }
    h2 {
        margin-bottom: 25px;
        color: #005bbb;
        font-weight: 700;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    input[type="number"],
    input[type="password"] {
        padding: 12px 15px;
        border: 1.8px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input[type="number"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #005bbb;
        box-shadow: 0 0 8px rgba(0,91,187,0.4);
    }
    button {
        padding: 12px;
        background-color: #005bbb;
        color: white;
        font-size: 1.1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #003f7f;
    }
    p {
        margin-top: 20px;
        font-size: 0.95rem;
    }
    p a {
        color: #005bbb;
        text-decoration: none;
        font-weight: 600;
    }
    p a:hover {
        text-decoration: underline;
    }
    .error-message {
        color: #d93025;
        font-weight: 600;
        margin-bottom: 15px;
    }

    @media (max-width: 400px) {
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
        <h2>Connexion Admin</h2>
        <?php if (!empty($erreur)) : ?>
            <div class="error-message"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="number" name="id" placeholder="ID Admin" required />
            <input type="password" name="code" placeholder="Mot de passe" required />
            <button type="submit">Se connecter</button>
        </form>
        <p><a href="../index.php">Retour à l'accueil</a></p>
    </div>
</body>
</html>
