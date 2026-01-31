<?php
require "db.php";

// Verific dacă coloana deja există
$result = $conn->query("SHOW COLUMNS FROM loans LIKE 'imprumut_date'");

if ($result->num_rows == 0) {
    // Coloana nu există, o adaug
    if ($conn->query("ALTER TABLE loans ADD COLUMN imprumut_date DATE DEFAULT NULL")) {
        echo "✅ Coloana 'imprumut_date' a fost adăugată cu succes!";
    } else {
        echo "❌ Eroare la adăugarea coloanei: " . $conn->error;
    }
} else {
    echo "ℹ️ Coloana 'imprumut_date' deja există!";
}
?>
