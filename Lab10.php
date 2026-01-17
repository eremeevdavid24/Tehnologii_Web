<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Număr Prim Maxim</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef1f5;
        display: flex;
        justify-content: center;
        margin-top: 50px;
    }
    .container {
        background: white;
        padding: 25px;
        width: 420px;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0,0,0,0.2);
    }
    input {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border: 2px solid #444;
        border-radius: 8px;
        font-size: 16px;
    }
    button {
        width: 100%;
        margin-top: 15px;
        padding: 12px;
        background: #0080ff;
        color: white;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    button:hover {
        background: #005fcc;
    }
    .rezultat {
        margin-top: 20px;
        font-size: 18px;
        font-weight: bold;
    }
</style>
</head>
<body>
<div class="container">
<h2>Componenta maximă dintre cele prime</h2>
<form method="post">
    <label>Introdu valoarea lui n (max 100):</label>
    <input type="number" name="n" min="1" max="100" required>
    <button type="submit">Generează și calculează</button>
</form>
<?php
function estePrim($x) {
    if ($x < 2) return false;
    if ($x == 2) return true;
    if ($x % 2 == 0) return false;
    for ($i = 3; $i * $i <= $x; $i += 2) {
        if ($x % $i == 0) return false;
    }
    return true;
}
if (isset($_POST["n"])) {
    $n = (int)$_POST["n"];
    if ($n < 1 || $n > 100) {
        echo "<div class='rezultat'>n trebuie să fie între 1 și 100!</div>";
    } else {
        $vector = [];
        for ($i = 0; $i < $n; $i++) {
            $vector[] = rand(1, 999);
        }
        echo "<p><b>Vector generat:</b><br>";
        echo implode(", ", $vector);
        echo "</p>";
        $maxPrim = -1;
        foreach ($vector as $x) {
            if (estePrim($x) && $x > $maxPrim) {
                $maxPrim = $x;
            }
        }
        if ($maxPrim == -1) {
            echo "<div class='rezultat'>Nu există numere prime în vector.</div>";
        } else {
            echo "<div class='rezultat'>Numărul prim maxim este: <b>$maxPrim</b></div>";
        }
    }
}
?>
</div>
</body>
</html>
