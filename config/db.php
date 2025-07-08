<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'projet_de_livraison';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>