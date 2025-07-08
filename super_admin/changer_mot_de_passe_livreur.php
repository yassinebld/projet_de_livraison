<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login/login_super_admin.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = intval($_GET['id']);
$message = "";

// Récupération du livreur
$stmt = $conn->prepare("SELECT nom, prenom FROM livreurs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$livreur = $result->fetch_assoc();

if (!$livreur) {
    die("Livreur introuvable.");
}

// Traitement
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_pass = $_POST['nouveau_mdp'] ?? '';
    $confirm_pass = $_POST['confirmer_mdp'] ?? '';

    if (empty($new_pass) || empty($confirm_pass)) {
        $message = "❌ Tous les champs sont requis.";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "❌ Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE livreurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $id);
        if ($stmt->execute()) {
            $message = "✅ Mot de passe modifié avec succès.";
        } else {
            $message = "❌ Erreur lors de la mise à jour.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mot de passe Livreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo {
            height: 70px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Logo et Titre -->
    <div class="text-center mb-4">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo" class="logo mb-2">
        <h3>Changer le mot de passe de <?= htmlspecialchars($livreur['nom']) ?> <?= htmlspecialchars($livreur['prenom']) ?></h3>
    </div>

    <!-- Formulaire -->
    <div class="card p-4 shadow-sm form-container">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="confirmer_mdp" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between">
                <a href="livreurs.php" class="btn btn-secondary">← Retour</a>
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>

        <?php if ($message): ?>
            <div class="alert mt-3 <?= strpos($message, '✅') === 0 ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
