<?php
session_start();
require_once('../config/db.php');

// ✅ Vérification de la session admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// ✅ Vérification du paramètre 'code'
if (!isset($_GET['code']) || empty(trim($_GET['code']))) {
    header("Location: dashboard.php");
    exit();
}

$code_commande = trim($_GET['code']);
$message = "";

// ✅ Récupérer la commande et son état
$stmtCmd = $conn->prepare("SELECT code, etat FROM commandes WHERE code = ?");
$stmtCmd->bind_param("s", $code_commande);
$stmtCmd->execute();
$resultCmd = $stmtCmd->get_result();

if ($resultCmd->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$commande = $resultCmd->fetch_assoc();

// ✅ Bloquer l'assignation si la commande n'est plus modifiable
$etats_non_assignables = [
    'prise par livreur',
    'livrée',
    'non livrée (destinataire introuvable)',
    'retournée au client'
];

$block_form = in_array($commande['etat'], $etats_non_assignables);

if ($block_form) {
    $message = "⚠️ Cette commande ne peut plus être assignée car elle est dans l'état : " . htmlspecialchars($commande['etat']);
}

// ✅ Liste des livreurs actifs avec ID, nom et prénom
$livreurs = [];
$result = $conn->query("SELECT id, nom, prenom FROM livreurs WHERE is_active = 1 ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $livreurs[] = $row;
    }
}

// ✅ Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$block_form) {
    if (!isset($_POST['livreur_id']) || !is_numeric($_POST['livreur_id'])) {
        $message = "Veuillez sélectionner un livreur valide.";
    } else {
        $livreur_id = intval($_POST['livreur_id']);

        // ✅ Vérifier si le livreur existe et est actif
        $stmtLiv = $conn->prepare("SELECT id FROM livreurs WHERE id = ? AND is_active = 1");
        $stmtLiv->bind_param("i", $livreur_id);
        $stmtLiv->execute();
        $resultLiv = $stmtLiv->get_result();

        if ($resultLiv->num_rows === 0) {
            $message = "Livreur sélectionné invalide.";
        } else {
            // ✅ Mise à jour : Assigner le livreur, enregistrer l'admin et VIDER le super admin
            $stmtUpdate = $conn->prepare("UPDATE commandes SET livreur_id = ?, assigne_par_admin = ?, assigne_par_super_admin = NULL WHERE code = ?");
            $stmtUpdate->bind_param("iis", $livreur_id, $admin_id, $code_commande);

            if ($stmtUpdate->execute()) {
                header("Location: commandes.php?message=Commande+assignée+avec+succès");
                exit();
            } else {
                $message = "Erreur lors de l'assignation.";
            }
            $stmtUpdate->close();
        }
        $stmtLiv->close();
    }
}

$stmtCmd->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Assigner Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #004080;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        header img {
            height: 50px;
            margin-right: 15px;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #004080;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        select, button {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #004080;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #0066cc;
        }
        .message {
            color: red;
            margin-top: 15px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #004080;
        }
    </style>
</head>
<body>

<header>
    <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Groupe Poste Maroc">
    <h2>Assigner une commande</h2>
</header>

<div class="container">
    <h1>Commande #<?= htmlspecialchars($commande['code']) ?></h1>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!$block_form): ?>
        <form method="POST">
            <label for="livreur_id">Sélectionner un livreur :</label>
            <select name="livreur_id" id="livreur_id" required>
                <option value="">-- Choisir un livreur --</option>
                <?php foreach ($livreurs as $livreur): ?>
                    <option value="<?= (int)$livreur['id'] ?>">
                        <?= "ID: " . (int)$livreur['id'] . " - " . htmlspecialchars($livreur['nom'] . " " . $livreur['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Assigner</button>
        </form>
    <?php else: ?>
        <p class="message">L'assignation est bloquée pour cette commande.</p>
    <?php endif; ?>

    <a class="back-link" href="commandes.php">← Retour</a>
</div>

</body>
</html>
