<?php
// Génère un code unique basé sur la date et l'heure + un suffixe aléatoire
function generateUniqueCode() {
    return date('YmdHis') . rand(100, 999); // ex: 20250616123045123
}

// Calcule le prix selon le poids (exemple : 10dh par kg)
function calculerPrix($poids) {
    return $poids * 10;
}

// Nettoyer une chaîne pour éviter XSS
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>
