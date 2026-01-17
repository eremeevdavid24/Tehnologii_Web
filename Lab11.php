<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Matrice A(n, n)</title>
<style>
    body{
        font-family: Arial, sans-serif;
        background:#f3f3f3;
        display:flex;
        justify-content:center;
        margin-top:40px;
    }
    .box{
        background:white;
        padding:20px;
        border-radius:10px;
        box-shadow:0 0 10px rgba(0,0,0,0.2);
        width:700px;
    }
    input{
        padding:8px;
        width:100px;
        border:2px solid #333;
        border-radius:6px;
        font-size:16px;
    }
    button{
        padding:9px 15px;
        margin-left:10px;
        border:none;
        border-radius:6px;
        background:#007bff;
        color:white;
        font-size:15px;
        cursor:pointer;
    }
    button:hover{ background:#005fcc; }
    h3{ margin-top:20px; }
    table{
        border-collapse:collapse;
        margin-top:5px;
    }
    td{
        border:1px solid #444;
        padding:4px 8px;
        text-align:center;
        min-width:25px;
    }
</style>
</head>
<body>
<div class="box">
<h2>Construirea matricei A(n, n)</h2>
<form method="post">
    <label>n = </label>
    <input type="number" name="n" min="1" max="20" required>
    <button type="submit">Generează</button>
</form>
<?php
function afiseazaMatrice($mat, $titlu){
    echo "<h3>$titlu</h3>";
    echo "<table>";
    foreach($mat as $linie){
        echo "<tr>";
        foreach($linie as $val){
            echo "<td>$val</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
if(isset($_POST["n"])){
    $n = (int)$_POST["n"];
    if($n < 1 || $n > 20) return;
    $A = [];
    for($i=0;$i<$n;$i++){
        for($j=0;$j<$n;$j++){
            $A[$i][$j] = ($i == $j) ? ($i + 1) : 0;
        }
    }
    $B = [];
    for($i=1;$i<=$n;$i++){
        for($j=1;$j<=$n;$j++){
            if($i <= $j) $B[$i-1][$j-1] = $n - ($j - $i);
            else         $B[$i-1][$j-1] = 1;
        }
    }
    $C = [];
    for($i=1;$i<=$n;$i++){
        for($j=1;$j<=$n;$j++){
            if($j <= $n - $i) $C[$i-1][$j-1] = 0;
            else              $C[$i-1][$j-1] = $j - ($n - $i);
        }
    }
    afiseazaMatrice($A, "a) matricea A");
    afiseazaMatrice($B, "b) matricea A");
    afiseazaMatrice($C, "c) matricea A");
}
?>
</div>
</body>
</html>
