<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: ../login/login_super_admin.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: livreurs.php");
    exit();
}

$id = intval($_GET['id']);

// Vérifier que le livreur existe et est actif
$stmt = $conn->prepare("SELECT is_active FROM livreurs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Livreur introuvable.";
    header("Location: livreurs.php");
    exit();
}

$livreur = $result->fetch_assoc();

if (!$livreur['is_active']) {
    $_SESSION['error'] = "Ce livreur est déjà désactivé.";
    header("Location: livreurs.php");
    exit();
}

// Désactiver le livreur (soft delete)
$update = $conn->prepare("UPDATE livreurs SET is_active = FALSE WHERE id = ?");
$update->bind_param("i", $id);
if ($update->execute()) {
    $_SESSION['success'] = "Livreur désactivé avec succès.";
} else {
    $_SESSION['error'] = "Erreur lors de la désactivation.";
}

header("Location: livreurs.php");
exit();
