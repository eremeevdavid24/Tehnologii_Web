<?php
// Conexiune la baza de date
$conn = new mysqli('localhost', 'root', '', 'biblioteca');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
