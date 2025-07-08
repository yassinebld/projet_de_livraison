-- TABLE villes_maroc
CREATE TABLE villes_maroc (
    nom VARCHAR(100) NOT NULL PRIMARY KEY
);

-- TABLE clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    cin VARCHAR(20) UNIQUE NOT NULL,
    ville VARCHAR(100) NOT NULL,
    adresse TEXT NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ville) REFERENCES villes_maroc(nom)
);

-- TABLE super_admin
CREATE TABLE super_admin (
    id VARCHAR(50) PRIMARY KEY,  -- Exemple : "superadmin"
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    cin VARCHAR(20) UNIQUE NOT NULL
);

-- TABLE admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    cin VARCHAR(20) UNIQUE NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    ville VARCHAR(100) NOT NULL,
    cree_par VARCHAR(50) NOT NULL, -- super_admin.id
    FOREIGN KEY (ville) REFERENCES villes_maroc(nom),
    FOREIGN KEY (cree_par) REFERENCES super_admin(id)
);

-- TABLE livreurs
CREATE TABLE livreurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    cin VARCHAR(20) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    cree_par_super_admin VARCHAR(50) NULL,
    cree_par_admin INT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (ville) REFERENCES villes_maroc(nom),
    FOREIGN KEY (cree_par_super_admin) REFERENCES super_admin(id),
    FOREIGN KEY (cree_par_admin) REFERENCES admins(id)
);

-- TABLE commandes
CREATE TABLE commandes (
    code VARCHAR(20) PRIMARY KEY,
    client_id INT NOT NULL,
    livreur_id INT DEFAULT NULL,
    poids INT NOT NULL,
    prix INT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    nom_destinataire VARCHAR(50) NOT NULL,
    prenom_destinataire VARCHAR(50) NOT NULL,
    numero_destinataire VARCHAR(20) NOT NULL,
    ville_destinataire VARCHAR(100) NOT NULL,
    adresse_destinataire TEXT NOT NULL,
    assigne_par_super_admin VARCHAR(50) NULL,
    assigne_par_admin INT NULL,

    etat ENUM(
        'en attente',
        'prise par livreur',
        'non prise (client introuvable)',
        'livrée',
        'non livrée (destinataire introuvable)',
        'retournée au client'
    ) DEFAULT 'en attente',

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (livreur_id) REFERENCES livreurs(id) ON DELETE SET NULL,
    FOREIGN KEY (ville_destinataire) REFERENCES villes_maroc(nom),
    FOREIGN KEY (assigne_par_super_admin) REFERENCES super_admin(id),
    FOREIGN KEY (assigne_par_admin) REFERENCES admins(id)
);





-- pour avoir les code hasher
<?php
$mot_de_passe_clair = 'superadmin';
$hash = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);
echo $hash;
?>

--pour inserer les donne 
INSERT INTO super_admin (id, mot_de_passe, nom, prenom, cin) VALUES
('1', '$2y$10$AZw./9uMV9VXGWgohbg4wO06xG9OxZXdaIQBr4g8FFckcMLSxj/ie' , 'xxx' , 'xxx' , 'ce32');


-- INSERTION des villes du Maroc 
INSERT INTO villes_maroc (nom) VALUES
('Agadir'), ('Ahfir'), ('Aïn Harrouda'), ('Ait Melloul'), ('Al Hoceïma'), ('Al Jadida'),
('Aourir'), ('Arfoud'), ('Asilah'), ('Azemmour'), ('Azrou'), ('Beni Mellal'), ('Berkane'),
('Berrechid'), ('Bouskoura'), ('Bouznika'), ('Casablanca'), ('Chefchaouen'), ('Chichaoua'),
('Dakhla'), ('Driouch'), ('El Hajeb'), ('El Kelaâ des Sraghna'), ('Erfoud'), ('Errachidia'),
('Essaouira'), ('Fès'), ('Fquih Ben Salah'), ('Fnideq'), ('Guelmim'), ('Guercif'),
('Ifrane'), ('Imzouren'), ('Inezgane'), ('Jerada'), ('Kariat Ba Mohamed'), ('Kasba Tadla'),
('Kénitra'), ('Khemisset'), ('Khenifra'), ('Khouribga'), ('Laâyoune'), ('Larache'),
('Marrakech'), ('Martil'), ('Meknès'), ('Midelt'), ('Mohammédia'), ('Moulay Bousselham'),
('Nador'), ('Ouarzazate'), ('Oued Zem'), ('Oujda'), ('Rabat'), ('Safi'), ('Salé'),
('Sefrou'), ('Settat'), ('Sidi Bennour'), ('Sidi Ifni'), ('Sidi Kacem'), ('Sidi Slimane'),
('Skhirat'), ('Souk El Arbaa'), ('Tamesna'), ('Tan-Tan'), ('Taounate'), ('Taourirt'),
('Tarfaya'), ('Taroudant'), ('Taza'), ('Temara'), ('Tétouan'), ('Tiflet'), ('Tinghir'),
('Tiznit'), ('Youssoufia'), ('Zagora');
