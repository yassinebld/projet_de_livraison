<?php
session_start();
require_once('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifiant = trim($_POST['identifiant']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM clients WHERE email = ? OR telephone = ?");
    if (!$stmt) {
        die("Erreur de préparation : " . $conn->error);
    }
    $stmt->bind_param("ss", $identifiant, $identifiant);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            header("Location: ../client/dashboard.php");
            exit();
        } else {
            $erreur = "Mot de passe incorrect.";
        }
    } else {
        $erreur = "Identifiants incorrects.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion Client</title>
    <style>
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
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        .login-container img.logo {
            width: 100px;
            margin-bottom: 15px;
        }

        .login-container h2 {
            margin-bottom: 25px;
            color: #0066cc;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-container button:hover {
            background-color: #004c99;
        }

        .login-container p {
            text-align: center;
            margin-top: 15px;
        }

        .login-container a {
            color: #0066cc;
            text-decoration: none;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .error-message {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo affiché en haut -->
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Barid Poste" class="logo" />

        <h2>Connexion Client</h2>

        <?php if (!empty($erreur)) : ?>
            <div class="error-message"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="identifiant" placeholder="Email ou Téléphone" required />
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required />
            <button type="submit">Se connecter</button>
        </form>

        <p>
            <a href="../login/register_client.php">Pas encore inscrit ? Inscrivez-vous</a><br>
            <a href="../index.php">Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
