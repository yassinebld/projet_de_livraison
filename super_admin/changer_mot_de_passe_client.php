<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header('Location: ../login_super_admin.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$client_id = intval($_GET['id']);
$message = "";

// Récupérer les informations du client
$stmt = $conn->prepare("SELECT nom, prenom FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    die("Client introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (strlen($new_password) < 6) {
        $message = "❌ Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE clients SET mot_de_passe = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $client_id);
        if ($stmt->execute()) {
            $message = "✅ Mot de passe modifié avec succès.";
        } else {
            $message = "❌ Erreur lors de la modification.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mot de passe Client</title>
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
        <h3>Changer le mot de passe de <?= htmlspecialchars($client['nom']) ?> <?= htmlspecialchars($client['prenom']) ?></h3>
    </div>

    <!-- Formulaire -->
    <div class="card p-4 shadow-sm form-container">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <div class="d-flex justify-content-between">
                <a href="clients.php" class="btn btn-secondary">← Retour</a>
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
