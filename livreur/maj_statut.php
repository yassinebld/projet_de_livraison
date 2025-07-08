<?php
session_start();
require_once "../config/db.php";

// Vérifier que le livreur est connecté
if (!isset($_SESSION['livreur']) || !isset($_SESSION['livreur']['id'])) {
    header("Location: ../login/login_livreur.php");
    exit();
}

// Traiter la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_commande = trim($_POST['code_commande'] ?? '');
    $etat = trim($_POST['etat'] ?? '');

    // Liste des états valides
    $etats_valides = [
        'en attente',
        'prise par livreur',
        'non prise (client introuvable)',
        'livrée',
        'non livrée (destinataire introuvable)',
        'retournée au client'
    ];

    // Vérifier la validité des données
    if ($code_commande === '' || !in_array($etat, $etats_valides, true)) {
        header("Location: dashboard.php");
        exit();
    }

    $livreur_id = $_SESSION['livreur']['id'];

    // Vérifier que la commande appartient à ce livreur
    $stmt = $conn->prepare("SELECT code FROM commandes WHERE code = ? AND livreur_id = ?");
    $stmt->bind_param("si", $code_commande, $livreur_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si la commande existe et est bien assignée au livreur
    if ($result->num_rows === 1) {
        $update = $conn->prepare("UPDATE commandes SET etat = ? WHERE code = ?");
        $update->bind_param("ss", $etat, $code_commande);
        $update->execute();
        $update->close();
    }

    $stmt->close();
}

// Rediriger vers le dashboard
header("Location: dashboard.php");
exit();
