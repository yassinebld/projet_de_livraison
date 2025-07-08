<?php
session_start();
require_once('../config/db.php');

// Vérifier que l'utilisateur est connecté en super admin
if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login_super_admin.php");
    exit();
}

// Vérifier que le code est présent dans l'URL
if (!isset($_GET['code']) || empty(trim($_GET['code']))) {
    $_SESSION['error'] = "Code de commande manquant.";
    header("Location: dashboard.php");
    exit();
}

$code = trim($_GET['code']);

// Vérifier que la commande existe, est active et en état "en attente"
$stmt = $conn->prepare("SELECT etat, is_active FROM commandes WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Commande introuvable.";
    header("Location: dashboard.php");
    exit();
}

$commande = $result->fetch_assoc();

if (!$commande['is_active']) {
    $_SESSION['error'] = "Cette commande est déjà supprimée.";
    header("Location: dashboard.php");
    exit();
}

if ($commande['etat'] !== 'en attente') {
    $_SESSION['error'] = "Impossible de supprimer une commande qui n'est pas en attente.";
    header("Location: dashboard.php");
    exit();
}

// Désactiver la commande (soft delete)
$stmt = $conn->prepare("UPDATE commandes SET is_active = FALSE WHERE code = ?");
$stmt->bind_param("s", $code);

if ($stmt->execute()) {
    $_SESSION['success'] = "Commande désactivée avec succès.";
} else {
    $_SESSION['error'] = "Erreur lors de la désactivation de la commande.";
}

header("Location: dashboard.php");
exit();
