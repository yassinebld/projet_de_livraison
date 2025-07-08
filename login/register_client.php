<?php
require_once('../config/db.php');

// Récupérer la liste des villes depuis la base de données
$villes = [];
$result = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom");
while ($row = $result->fetch_assoc()) {
    $villes[] = $row['nom'];
}

$messages = []; // pour afficher erreurs ou succès

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $cin = trim($_POST['cin']);
    $ville = trim($_POST['ville']);
    $adresse = trim($_POST['adresse']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $erreurs = [];

    // Validation téléphone : exactement 10 chiffres, uniquement chiffres
    if (!preg_match('/^\d{10}$/', $telephone)) {
        $erreurs[] = "Le numéro de téléphone doit contenir exactement 10 chiffres et uniquement des nombres.";
    }

    // Vérifier email
    $check_email = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) {
        $erreurs[] = "Email déjà utilisé.";
    }
    $check_email->close();

    // Vérifier téléphone
    $check_tel = $conn->prepare("SELECT id FROM clients WHERE telephone = ?");
    $check_tel->bind_param("s", $telephone);
    $check_tel->execute();
    $check_tel->store_result();
    if ($check_tel->num_rows > 0) {
        $erreurs[] = "Téléphone déjà utilisé.";
    }
    $check_tel->close();

    // Vérifier CIN
    $check_cin = $conn->prepare("SELECT id FROM clients WHERE cin = ?");
    $check_cin->bind_param("s", $cin);
    $check_cin->execute();
    $check_cin->store_result();
    if ($check_cin->num_rows > 0) {
        $erreurs[] = "CIN déjà utilisé.";
    }
    $check_cin->close();

    if (!empty($erreurs)) {
        $messages = $erreurs;
    } else {
        $stmt = $conn->prepare("INSERT INTO clients (nom, prenom, email, telephone, cin, ville, adresse, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nom, $prenom, $email, $telephone, $cin, $ville, $adresse, $mot_de_passe);

        if ($stmt->execute()) {
            $messages[] = "<span class='success'>Inscription réussie. <a href='login_client.php'>Se connecter</a></span>";
        } else {
            $messages[] = "<span class='error'>Erreur lors de l'inscription.</span>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Inscription Client</title>
    <style>
        /* Reset et base */
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


        .container {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.25);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }

        .container img.logo {
            width: 100px;
            margin-bottom: 25px;
        }

        h2 {
            margin-bottom: 30px;
            color: #0066cc;
            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 12px 15px;
            border: 1.8px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 8px rgba(0, 102, 204, 0.4);
        }

        button {
            padding: 12px;
            background-color: #0066cc;
            color: white;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #004c99;
        }

        p.links {
            margin-top: 20px;
            font-size: 0.95rem;
        }

        p.links a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
        }

        p.links a:hover {
            text-decoration: underline;
        }

        /* Messages */
        .messages {
            margin-bottom: 20px;
            text-align: center;
        }

        .messages p,
        .messages span {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .messages .error {
            color: #d93025;
        }

        .messages .success {
            color: #188038;
        }

        /* Responsive */
        @media (max-width: 500px) {
            .container {
                padding: 30px 20px;
                max-width: 90vw;
            }
        }
    </style>
    <script>
        // Empêche la saisie de caractères non numériques dans le champ téléphone
        function validatePhoneInput(event) {
            const allowedKeys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab'];
            if (!/^\d$/.test(event.key) && !allowedKeys.includes(event.key)) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Barid Poste" class="logo" />
        <h2>Inscription Client</h2>

        <div class="messages">
            <?php
            if (!empty($messages)) {
                foreach ($messages as $msg) {
                    echo "<p>$msg</p>";
                }
            }
            ?>
        </div>

        <form method="POST" action="">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input 
                type="text" 
                name="telephone" 
                placeholder="Numéro de téléphone" 
                required 
                pattern="\d{10}" 
                maxlength="10" 
                inputmode="numeric" 
                title="Veuillez saisir exactement 10 chiffres."
                onkeydown="validatePhoneInput(event)"
            >
            <input type="text" name="cin" placeholder="CIN" required>

            <select name="ville" required>
                <option value="">-- Sélectionnez une ville --</option>
                <?php foreach ($villes as $v) : ?>
                    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="adresse" placeholder="Adresse exacte" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>

        <p class="links"><a href="login_client.php">Déjà inscrit ? Connectez-vous</a></p>
        <p><a href="../index.php">Retour à l'accueil</a></p>
    </div>
</body>
</html>
