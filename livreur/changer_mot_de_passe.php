<?php
session_start();
require_once('../config/db.php');

// Vérifier que livreur est connecté
if (!isset($_SESSION['livreur']) || !isset($_SESSION['livreur']['id'])) {
    header("Location: ../login/login_livreur.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

    if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
        $message = "❌ Veuillez remplir tous les champs.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $message = "❌ Le nouveau mot de passe ne correspond pas à la confirmation.";
    } else {
        $livreur_id = $_SESSION['livreur']['id'];

        $stmt = $conn->prepare("SELECT mot_de_passe FROM livreurs WHERE id = ?");
        if (!$stmt) {
            $message = "❌ Erreur SQL : " . $conn->error;
        } else {
            $stmt->bind_param("i", $livreur_id);
            $stmt->execute();
            $stmt->bind_result($mdp_hash);

            if ($stmt->fetch()) {
                $stmt->close();

                if (password_verify($ancien_mdp, $mdp_hash)) {
                    $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

                    $update = $conn->prepare("UPDATE livreurs SET mot_de_passe = ? WHERE id = ?");
                    if (!$update) {
                        $message = "❌ Erreur SQL : " . $conn->error;
                    } else {
                        $update->bind_param("si", $nouveau_hash, $livreur_id);
                        $update->execute();
                        $update->close();

                        $message = "✅ Mot de passe changé avec succès.";
                    }
                } else {
                    $message = "❌ Ancien mot de passe incorrect.";
                }
            } else {
                $message = "❌ Livreur introuvable.";
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Changer mot de passe Livreur</title>
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
        <h3>Changer votre mot de passe</h3>
    </div>

    <!-- Formulaire -->
    <div class="card p-4 shadow-sm form-container">
        <?php if (!empty($message)): ?>
            <div class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="ancien_mdp" class="form-label">Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" id="ancien_mdp" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="nouveau_mdp" class="form-label">Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" id="nouveau_mdp" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="confirmer_mdp" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirmer_mdp" id="confirmer_mdp" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">← Retour</a>
                <button type="submit" class="btn btn-primary">Changer</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
