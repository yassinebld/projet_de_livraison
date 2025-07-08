<?php
session_start();
require_once('../config/db.php');

// Vérifier que le super admin est connecté
if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login/login_super_admin.php");
    exit();
}

$super_admin_id = $_SESSION['super_admin']; // On récupère l'ID du super admin connecté

$message = "";

// Charger les villes
$villes = [];
$result = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $villes[] = $row['nom'];
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($conn->real_escape_string($_POST['nom']));
    $prenom = trim($conn->real_escape_string($_POST['prenom']));
    $telephone = trim($conn->real_escape_string($_POST['telephone']));
    $cin = trim($conn->real_escape_string($_POST['cin']));
    $ville = trim($conn->real_escape_string($_POST['ville']));
    $mot_de_passe = $_POST['mot_de_passe'];

    if (!preg_match('/^\d{10}$/', $telephone)) {
        $message = "❌ Le numéro de téléphone doit contenir exactement 10 chiffres.";
    } else {
        $check = $conn->prepare("SELECT id FROM livreurs WHERE cin = ? OR telephone = ?");
        $check->bind_param("ss", $cin, $telephone);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Un livreur avec ce CIN ou téléphone existe déjà.";
        } else {
            $mot_de_passe_hashed = password_hash($mot_de_passe, PASSWORD_BCRYPT);
            // ⚡️ Insertion avec l'identifiant du super admin
            $stmt = $conn->prepare("INSERT INTO livreurs (nom, prenom, telephone, cin, mot_de_passe, ville, cree_par_super_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nom, $prenom, $telephone, $cin, $mot_de_passe_hashed, $ville, $super_admin_id);

            if ($stmt->execute()) {
                $message = "✅ Livreur ajouté avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout du livreur : " . $stmt->error;
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
    <title>Ajouter un Livreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo {
            height: 70px;
        }
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <!-- Logo et Titre -->
    <div class="text-center mb-4">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo" class="logo mb-2">
        <h2>Ajouter un Livreur</h2>
    </div>

    <!-- Message d'information -->
    <?php if ($message): ?>
        <div class="alert <?= strpos($message, '✅') === 0 ? 'alert-success' : 'alert-danger' ?> text-center">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="card shadow-sm p-4 form-container">
        <form method="POST">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" name="nom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone :</label>
                <input type="text" name="telephone" class="form-control" required pattern="\d{10}" maxlength="10"
                       title="Entrez exactement 10 chiffres" inputmode="numeric">
            </div>

            <div class="mb-3">
                <label for="cin" class="form-label">CIN :</label>
                <input type="text" name="cin" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="ville" class="form-label">Ville :</label>
                <select name="ville" class="form-select" required>
                    <option value="">-- Sélectionnez une ville --</option>
                    <?php foreach ($villes as $ville): ?>
                        <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe :</label>
                <input type="password" name="mot_de_passe" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="livreurs.php" class="btn btn-secondary">← Retour</a>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
