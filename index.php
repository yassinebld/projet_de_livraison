<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Accueil - Site de Livraison</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('images/barid_poste_autre.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
            color: white;
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar a {
            margin: 0 10px;
            color: #f0f0f0;
            text-decoration: none;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .navbar a:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: #000;
        }

        .main-content {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding-top: 80px;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.7));
        }

        h1 {
            margin-bottom: 30px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
            font-size: 2.5em;
        }

        .history {
            background-color: rgba(0, 0, 0, 0.6);
            margin: 50px auto;
            padding: 30px;
            width: 90%;
            max-width: 900px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.7);
            font-size: 1.1em;
            line-height: 1.6em;
        }

        .history h2 {
            color: rgb(61, 59, 209);
            margin-top: 20px;
        }

        .history ul {
            padding-left: 20px;
        }

        .history li {
            margin-bottom: 8px;
        }

    </style>
</head>
<body>

    <!-- Barre de navigation -->
    <div class="navbar">
        <a href="login/login_client.php">Connexion Client</a>
        <a href="login/register_client.php">Inscription Client</a>
        <a href="login/login_admin.php">Connexion Admin</a>
        <a href="login/login_livreur.php">Connexion Livreur</a>
        <a href="login/login_super_admin.php">Connexion Super Admin</a>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        <!-- Logo -->
        <img src="images/groupe_poste_maroc_logo.jpg" alt="Logo Barid Poste" class="logo">

        <!-- Message de bienvenue -->
        <h1>Bienvenue sur le site de livraison Al Barid</h1>
    </div>

    <!-- Bloc Histoire de Barid Poste -->
    <div class="history">
        <h2>🏛️ Origines historiques</h2>
        <p>L’histoire de la poste au Maroc remonte au 9ᵉ siècle, avec un service postal rudimentaire sous forme de "Barid", un réseau de messagers à cheval utilisé par les dynasties pour transmettre des messages officiels et militaires.</p>
        <p>Mais le service postal moderne commence à se développer à la fin du 19ᵉ siècle, notamment avec la présence des puissances étrangères (comme la France et l’Espagne) qui ont mis en place leurs propres bureaux de poste.</p>

        <h2>📮 Création de Barid Al-Maghrib</h2>
        <p>En 1913, après l’établissement du Protectorat français, le Maroc commence à organiser un système postal national.</p>
        <p>Après l’indépendance du Maroc en 1956, l'État marocain reprend le contrôle du système postal.</p>
        <p>En 1998, la Poste marocaine est transformée en un établissement public autonome nommé Barid Al-Maghrib.</p>
        <p>En 2010, Barid Al-Maghrib lance sa propre banque : Al Barid Bank, pour démocratiser l’accès aux services bancaires.</p>

        <h2>🚀 Modernisation et digitalisation</h2>
        <ul>
            <li>La poste express (Barid Express)</li>
            <li>Le suivi de colis en ligne</li>
            <li>Les cyberpostes (services électroniques)</li>
            <li>Développement du e-commerce et de la livraison à domicile</li>
        </ul>

        <h2>📦 Services proposés</h2>
        <ul>
            <li>Envois postaux et colis nationaux/internationaux</li>
            <li>Services financiers via Al Barid Bank</li>
            <li>Services numériques (certificats électroniques, e-barid...)</li>
            <li>Services administratifs comme la lettre recommandée électronique ou la signature électronique</li>
        </ul>

        <h2>🌍 Rôle stratégique</h2>
        <ul>
            <li>Le désenclavement des zones rurales</li>
            <li>L’inclusion financière</li>
            <li>Le soutien à l'administration électronique marocaine (e-gov)</li>
        </ul>
    </div>

</body>
</html>
