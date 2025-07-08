<?php 
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_client.php");
    exit();
}

$client_id = $_SESSION['user_id'];
$message = "";

// Récupération des villes
$villes_result = $conn->query("SELECT nom FROM villes_maroc ORDER BY nom ASC");
$villes = [];
while ($row = $villes_result->fetch_assoc()) {
    $villes[] = $row['nom'];
}

function calculerPrix($poids) {
    if ($poids <= 10) return 50;
    elseif ($poids <= 20) return 60;
    elseif ($poids <= 30) return 70;
    elseif ($poids <= 40) return 80;
    elseif ($poids <= 50) return 90;
    elseif ($poids <= 60) return 100;
    elseif ($poids <= 70) return 110;
    elseif ($poids <= 80) return 120;
    elseif ($poids <= 90) return 130;
    elseif ($poids <= 100) return 140;
    else return 150;
}

function genererCodeUnique() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $poids = round(floatval($_POST['poids']), 1);
    $nom_destinataire = trim($_POST['nom_destinataire']);
    $prenom_destinataire = trim($_POST['prenom_destinataire']);
    $numero_destinataire = trim($_POST['destinataire_tel']);
    $ville_destinataire = trim($_POST['ville_destination']);
    $adresse_destinataire = trim($_POST['adresse_destination']);

    if ($poids < 0.1 || $poids > 100) {
        $message = "❌ Le poids doit être entre 0.1 et 100 kg.";
    } elseif (!preg_match('/^\d{10}$/', $numero_destinataire)) {
        $message = "❌ Le numéro de téléphone doit contenir exactement 10 chiffres.";
    } elseif (empty($nom_destinataire) || empty($prenom_destinataire) || empty($ville_destinataire) || empty($adresse_destinataire)) {
        $message = "❌ Veuillez remplir tous les champs.";
    } else {
        $prix = calculerPrix($poids);
        $code = genererCodeUnique();
        $etat = 'en attente';

        $stmt = $conn->prepare("INSERT INTO commandes 
            (code, client_id, poids, prix, nom_destinataire, prenom_destinataire, numero_destinataire, ville_destinataire, adresse_destinataire, etat, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("siidssssss", $code, $client_id, $poids, $prix, $nom_destinataire, $prenom_destinataire, $numero_destinataire, $ville_destinataire, $adresse_destinataire, $etat);

        if ($stmt->execute()) {
            $message = "✅ Commande passée avec succès. Code de suivi : <strong>$code</strong>";
        } else {
            $message = "❌ Erreur : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Passer une commande</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #003366;
            text-align: center;
        }

        a {
            color: #0055a5;
            text-decoration: none;
            margin: 0 10px;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background-color: #0055a5;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0078d7;
        }

        .message {
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        table.tarifs {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table.tarifs th, table.tarifs td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        table.tarifs th {
            background-color: #003366;
            color: white;
        }

        .header {
            text-align: center;
            margin-top: 20px;
        }

        .header img {
            height: 60px;
        }

        .price-preview {
            font-weight: bold;
            color: green;
        }
    </style>
    <script>
        function updatePrice() {
            const poids = parseFloat(document.getElementById('poids').value);
            let prix = 0;
            if (poids > 0 && poids <= 10) prix = 50;
            else if (poids <= 20) prix = 60;
            else if (poids <= 30) prix = 70;
            else if (poids <= 40) prix = 80;
            else if (poids <= 50) prix = 90;
            else if (poids <= 60) prix = 100;
            else if (poids <= 70) prix = 110;
            else if (poids <= 80) prix = 120;
            else if (poids <= 90) prix = 130;
            else if (poids <= 100) prix = 140;
            document.getElementById('prix_estime').innerText = prix + ' DH';
        }
    </script>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="../images/groupe_poste_maroc_logo.jpg" alt="Logo Poste Maroc">
        <h2>Passer une nouvelle commande</h2>
        <div>
            <a href="dashboard.php">🏠 Retour</a> | 
            <a href="../logout.php">🔒 Déconnexion</a>
        </div>
    </div>

    <?php if ($message): ?>
        <p class="message" style="color: <?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>;">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Poids (kg) :</label>
        <input type="number" step="0.1" id="poids" name="poids" required min="0.1" max="100" oninput="updatePrice()">
        <div class="price-preview">Prix estimé : <span id="prix_estime">0 DH</span></div>

        <label>Nom du destinataire :</label>
        <input type="text" name="nom_destinataire" required>

        <label>Prénom du destinataire :</label>
        <input type="text" name="prenom_destinataire" required>

        <label>Numéro de téléphone :</label>
        <input 
            type="text" 
            name="destinataire_tel" 
            required 
            pattern="\d{10}" 
            maxlength="10" 
            inputmode="numeric" 
            title="Veuillez saisir exactement 10 chiffres."
        >

        <label>Ville de destination :</label>
        <select name="ville_destination" required>
            <option value="">-- Sélectionner une ville --</option>
            <?php foreach ($villes as $ville): ?>
                <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Adresse exacte :</label>
        <textarea name="adresse_destination" required></textarea>

        <button type="submit">✅ Envoyer la commande</button>
    </form>

    <h3>📊 Tableau des tarifs :</h3>
    <table class="tarifs">
        <tr><th>Poids (kg)</th><th>Prix (DH)</th></tr>
        <?php for ($i = 0; $i < 100; $i += 10): ?>
            <tr>
                <td><?= $i + 0.001 ?> - <?= $i + 10 ?></td>
                <td><?= 50 + ($i / 10) * 10 ?></td>
            </tr>
        <?php endfor; ?>
    </table>
</div>

</body>
</html>
