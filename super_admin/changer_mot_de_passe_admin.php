<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login_super_admin.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = intval($_GET['id']);
$message = "";

// Vérifier si admin existe
$stmt = $conn->prepare("SELECT nom, prenom FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Admin introuvable.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

    if (empty($nouveau_mdp) || empty($confirmer_mdp)) {
        $message = "❌ Tous les champs sont requis.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $message = "❌ Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET mot_de_passe = ? WHERE id = ?");
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
    <title>Changer Mot de Passe - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0077b6, #00b4d8);
            min-height: 100vh;
            display: flex;
            align-items: start;
            justify-content: center;
            padding-top: 50px;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            max-width: 500px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .logo {
            max-height: 80px;
            display: block;
            margin: 0 auto 20px;
        }
        .alert {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="card p-4 bg-white">
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo" class="logo">

    <h4 class="text-center mb-4 text-primary">
        Changer le mot de passe de <br>
        <?= htmlspecialchars($admin['nom']) . ' ' . htmlspecialchars($admin['prenom']) ?>
    </h4>

    <?php if ($message): ?>
        <div class="alert <?= strpos($message, '✅') === 0 ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="nouveau_mdp" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="confirmer_mdp" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">🔒 Modifier</button>
        <a href="admins.php" class="btn btn-outline-secondary w-100 mt-2">← Retour</a>
    </form>
</div>

</body>
</html>
