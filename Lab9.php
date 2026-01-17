<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Determină aparițiile cifrelor</title>
<style>
    body{
        font-family: Arial, sans-serif;
        background:#eef1f5;
        display:flex;
        justify-content:center;
        margin-top:50px;
    }
    .box{
        background:white;
        padding:25px;
        border-radius:10px;
        width:380px;
        box-shadow:0 0 12px rgba(0,0,0,0.2);
    }
    input{
        width:100%;
        padding:10px;
        margin-top:10px;
        border:2px solid #444;
        border-radius:8px;
        font-size:16px;
    }
    button{
        margin-top:15px;
        width:100%;
        padding:12px;
        background:#0066ff;
        color:white;
        border:none;
        border-radius:8px;
        font-size:16px;
        cursor:pointer;
    }
    button:hover{
        background:#0040cc;
    }
    .rezultat{
        margin-top:20px;
        font-size:18px;
        font-weight:bold;
    }
</style>
</head>
<body>
<div class="box">
    <h2>Determină aparițiile cifrelor</h2>
    <form method="post">
        <label>Introdu un număr (max. 9 cifre):</label>
        <input type="number" name="numar" required>
        <button type="submit">Calculează</button>
    </form>
    <div class="rezultat">
        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $n = $_POST["numar"];
            if ($n < 0 || strlen($n) > 9) {
                echo "Număr invalid! (max 9 cifre)";
            } else {
                $copie = $n;
                $u = $n % 10;
                $z = intval(($n / 10) % 10);
                $contU = 0;
                $contZ = 0;
                while ($copie > 0) {
                    $c = $copie % 10;
                    if ($c == $u) $contU++;
                    if ($c == $z) $contZ++;
                    $copie = intval($copie / 10);
                }
                echo "Cifra unităților este <b>$u</b> și apare de <b>$contU</b> ori.<br>";
                echo "Cifra zecilor este <b>$z</b> și apare de <b>$contZ</b> ori.<br>";
            }
        }
        ?>
    </div>
</div>
</body>
</html>
