<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login/login_admin.php");
    exit();
}

if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: dashboard.php");
    exit();
}

$code = $_GET['code'];

// On vérifie l'état actuel de la commande
$stmt_check = $conn->prepare("SELECT etat FROM commandes WHERE code = ? AND is_active = TRUE");
$stmt_check->bind_param("s", $code);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // Commande introuvable ou déjà désactivée
    header("Location: dashboard.php?error=commande_introuvable");
    exit();
}

$commande = $result_check->fetch_assoc();

// Si l'état de la commande n'est pas "en attente", on interdit la désactivation
if ($commande['etat'] !== 'en attente') {
    header("Location: dashboard.php?error=etat_non_permis");
    exit();
}

// Désactivation de la commande (is_active = FALSE)
$stmt = $conn->prepare("UPDATE commandes SET is_active = FALSE WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();

header("Location: dashboard.php?success=commande_desactivee");
exit();
