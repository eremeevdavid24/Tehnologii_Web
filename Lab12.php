<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Inserare litera "o"</title>
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
        width:450px;
    }
    textarea{
        width:100%;
        height:80px;
        padding:8px;
        border:2px solid #333;
        border-radius:6px;
        font-size:16px;
        resize: vertical;
    }
    button{
        margin-top:10px;
        padding:10px 15px;
        border:none;
        border-radius:6px;
        background:#007bff;
        color:white;
        font-size:15px;
        cursor:pointer;
    }
    button:hover{
        background:#005fcc;
    }
    .rez{
        margin-top:15px;
        font-weight:bold;
        font-size:18px;
    }
</style>
</head>
<body>
<div class="box">
<h2>Inserare „o” înainte de „a” după „n”</h2>
<p>Introdu textul:</p>
<form method="post">
    <textarea name="txt" placeholder="ex: canal"><?php
        if(isset($_POST["txt"])) echo $_POST["txt"];
    ?></textarea>
    <br>
    <button type="submit">Transformă</button>
</form>
<?php
if(isset($_POST["txt"])){
    $s = $_POST["txt"];
    $r = "";
    for($i = 0; $i < strlen($s); $i++){
        if($s[$i] == 'a' && $i > 0 && $s[$i-1] == 'n'){
            $r .= 'o';
        }
        $r .= $s[$i];
    }
    echo "<div class='rez'>Rezultat: $r</div>";
}
?>
</div>
</body>
</html>
