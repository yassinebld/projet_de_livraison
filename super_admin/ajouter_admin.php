<?php 
session_start();
require_once('../config/db.php');

// Vérification de la session
if (!isset($_SESSION['super_admin'])) {
    header('Location: ../login/login_super_admin.php');
    exit();
}

// Récupération de l'ID du super admin connecté
$super_admin_id = $_SESSION['super_admin'];

$villes = [];
$result = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom");
while ($row = $result->fetch_assoc()) {
    $villes[] = $row['nom'];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $cin = trim($_POST['cin']);
    $telephone = trim($_POST['telephone']);
    $ville = trim($_POST['ville']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_conf = $_POST['mot_de_passe_conf'];

    if (empty($nom) || empty($prenom) || empty($cin) || empty($telephone) || empty($ville) || empty($mot_de_passe) || empty($mot_de_passe_conf)) {
        $message = "❌ Veuillez remplir tous les champs.";
    } elseif (!preg_match('/^[0-9]{10}$/', $telephone)) {
        $message = "❌ Le numéro de téléphone doit contenir exactement 10 chiffres.";
    } elseif ($mot_de_passe !== $mot_de_passe_conf) {
        $message = "❌ Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si le téléphone existe déjà
        $check_tel = $conn->prepare("SELECT id FROM admins WHERE telephone = ?");
        $check_tel->bind_param("s", $telephone);
        $check_tel->execute();
        $check_tel->store_result();

        // Vérifier si le CIN existe déjà
        $check_cin = $conn->prepare("SELECT id FROM admins WHERE cin = ?");
        $check_cin->bind_param("s", $cin);
        $check_cin->execute();
        $check_cin->store_result();

        if ($check_tel->num_rows > 0) {
            $message = "❌ Ce numéro de téléphone est déjà utilisé.";
        } elseif ($check_cin->num_rows > 0) {
            $message = "❌ Ce CIN est déjà utilisé.";
        } else {
            // Insertion de l'admin
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (nom, prenom, cin, mot_de_passe, telephone, ville, cree_par) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nom, $prenom, $cin, $hash, $telephone, $ville, $super_admin_id);

            if ($stmt->execute()) {
                $message = "✅ Admin ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout : " . $conn->error;
            }
            $stmt->close();
        }

        $check_tel->close();
        $check_cin->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #005a87, #00a1d6);
            padding: 40px 15px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: start;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px 35px;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #003366;
        }
        .form-container img.logo {
            display: block;
            margin: 0 auto 25px;
            max-height: 80px;
        }
        label {
            font-weight: 600;
            margin-top: 15px;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        button {
            margin-top: 25px;
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
        }
        .message {
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1.5px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1.5px solid #f5c6cb;
        }
        a.back {
            display: block;
            margin-bottom: 15px;
            color: #0071bc;
            text-decoration: none;
            font-weight: 600;
        }
        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <a href="admins.php" class="back">← Retour à la liste</a>

    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Groupe Poste Maroc" class="logo">

    <h2>Ajouter un administrateur</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" class="form-control" required>

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" class="form-control" required>

        <label for="cin">CIN :</label>
        <input type="text" id="cin" name="cin" class="form-control" required>

        <label for="telephone">Téléphone :</label>
        <input type="text" id="telephone" name="telephone" class="form-control" pattern="^[0-9]{10}$" title="Entrez exactement 10 chiffres" required>

        <label for="ville">Ville :</label>
        <select id="ville" name="ville" class="form-select" required>
            <option value="">-- Sélectionnez une ville --</option>
            <?php foreach ($villes as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>

        <label for="mot_de_passe_conf">Confirmer le mot de passe :</label>
        <input type="password" id="mot_de_passe_conf" name="mot_de_passe_conf" class="form-control" required>

        <button type="submit" class="btn btn-primary">➕ Ajouter</button>
    </form>
</div>

</body>
</html>
