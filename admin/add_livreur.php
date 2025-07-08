<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

$admin_id = $_SESSION['admin_id']; // On récupère l'ID de l'admin connecté
$message = "";

// Récupérer villes
$villes = [];
$result = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $villes[] = $row['nom'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $cin = $conn->real_escape_string($_POST['cin']);
    $ville = $conn->real_escape_string($_POST['ville']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (!preg_match('/^\d{10}$/', $telephone)) {
        $message = "Le numéro de téléphone doit contenir exactement 10 chiffres.";
    } else {
        $check = $conn->prepare("SELECT id FROM livreurs WHERE cin = ? OR telephone = ?");
        $check->bind_param("ss", $cin, $telephone);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Un livreur avec ce CIN ou téléphone existe déjà.";
        } else {
            $mot_de_passe_hashed = password_hash($mot_de_passe, PASSWORD_BCRYPT);

            // ✅ Insertion avec enregistrement de l'admin créateur
            $stmt = $conn->prepare("INSERT INTO livreurs (nom, prenom, telephone, cin, mot_de_passe, ville, cree_par_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nom, $prenom, $telephone, $cin, $mot_de_passe_hashed, $ville, $admin_id);

            if ($stmt->execute()) {
                $message = "✅ Livreur ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout du livreur.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un livreur</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            margin: 0; padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 40px;
        }

        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            max-width: 480px;
            width: 100%;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 20px;
            right: 20px;
            height: 50px;
            opacity: 0.8;
        }

        h1 {
            color: #003366;
            margin-bottom: 30px;
            text-align: center;
        }

        nav {
            text-align: center;
            margin-bottom: 25px;
        }

        nav a {
            color: #0077cc;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
            font-size: 14px;
        }
        nav a:hover {
            text-decoration: underline;
        }

        form label {
            display: block;
            margin: 12px 0 6px 0;
            font-weight: 600;
            color: #333;
        }

        form input[type="text"],
        form input[type="password"],
        form select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1.8px solid #ccc;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus,
        form input[type="password"]:focus,
        form select:focus {
            border-color: #0077cc;
            outline: none;
        }

        button {
            margin-top: 25px;
            width: 100%;
            background-color: #0055aa;
            color: #fff;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #003f7d;
        }

        .message {
            text-align: center;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 15px;
            user-select: none;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1.5px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1.5px solid #f5c6cb;
        }
    </style>

    <script>
        function validatePhoneInput(event) {
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
            if (!allowedKeys.includes(event.key) && !/^[0-9]$/.test(event.key)) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>

<div class="container">
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc" class="logo" />

    <h1>➕ Ajouter un livreur</h1>

    <nav>
        <a href="livreurs.php">← Retour</a>
        <a href="../logout.php">Déconnexion</a>
    </nav>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nom">Nom :</label>
        <input id="nom" type="text" name="nom" required />

        <label for="prenom">Prénom :</label>
        <input id="prenom" type="text" name="prenom" required />

        <label for="telephone">Téléphone :</label>
        <input
            id="telephone"
            type="text"
            name="telephone"
            required
            pattern="\d{10}"
            maxlength="10"
            inputmode="numeric"
            title="Veuillez saisir exactement 10 chiffres, uniquement des nombres."
            onkeydown="validatePhoneInput(event)"
        />

        <label for="cin">CIN :</label>
        <input id="cin" type="text" name="cin" required />

        <label for="ville">Ville :</label>
        <select id="ville" name="ville" required>
            <option value="" disabled selected>-- Choisissez une ville --</option>
            <?php foreach ($villes as $ville_option): ?>
                <option value="<?= htmlspecialchars($ville_option) ?>"><?= htmlspecialchars($ville_option) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="mot_de_passe">Mot de passe :</label>
        <input id="mot_de_passe" type="password" name="mot_de_passe" required />

        <button type="submit">Ajouter</button>
    </form>
</div>

</body>
</html>
