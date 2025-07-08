<?php
session_start();
require_once('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $code = $_POST['code'];

    // Recherche du livreur par ID
    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM livreurs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($livreur = $result->fetch_assoc()) {
        if (password_verify($code, $livreur['mot_de_passe'])) {
            $_SESSION['livreur'] = [
                'id' => $livreur['id'],
                'nom' => $livreur['nom']
            ];
            header("Location: ../livreur/dashboard.php");
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
<title>Connexion Livreur</title>
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
    input[type="number"],
    input[type="password"] {
        padding: 14px 18px;
        border: 1.8px solid #ccc;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input[type="number"]:focus,
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
    p {
        margin-top: 22px;
        font-size: 1rem;
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
        color: #d32f2f;
        font-weight: 700;
        margin-bottom: 18px;
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
        <h2>Connexion Livreur</h2>
        <?php if (!empty($erreur)) : ?>
            <div class="error-message"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="number" name="id" placeholder="ID Livreur" required />
            <input type="password" name="code" placeholder="Mot de passe" required />
            <button type="submit">Se connecter</button>
        </form>
        <p><a href="../index.php">Retour à l'accueil</a></p>
    </div>
</body>
</html>
