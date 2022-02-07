<?php
    session_start();
    $_SESSION['idFormation']="";
    require 'bdd/MySQL.inc.php';
    $db = MySQL::getInstance();
    if ($db == null) echo "Impossible de se connecter à la base de données !";
    else try 
    {
        $listFormations = $db->getFormations();
?>

 
 <head>
    <title>Formation l'ULHN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="formations.php">Formation</a>
    <a href="#">Litis</a>
    <a href="#">Stage</a>
    <a href="#">Profil</a>
</div>

<div id="main">
    <div class="navbar">
        <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776; Menu</span>
    </div>
    <div class="content">
        <h1>Liste des formations</h1>
        <div class="cards" id="listFormation">
            <?php 
            foreach($listFormations as $formations) 
            {
                echo('
                <a class="card" href="afficher.php?Formation='.$formations->getTitre_url().' ">
                <h2>'.$formations->getTitre().'</h2>
                <p>Durée : '.$formations->getDuree().' ans</p>
                <p>'.$formations->getPresentation().'</p>
                </a>
                ');
            }
            ?>
            <div class="card">FOUR</div>
            <div class="card">FIVE</div>
            <div class="card">SIX</div>
            <div class="card">SEVEN</div>
            <div class="card">EIGHT</div>
            <div class="card">NINE</div>
            <div class="card">TEN</div>
            <div class="card">ELEVEN</div>
            <div class="card">TWELVE</div>
        </div>
    </div>
</div>

<script>

function openNav() {
  document.getElementById("mySidenav").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
  document.getElementById("main").style.marginLeft= "0";
}
</script>

<?php
  }
  catch (Exception $e){ echo $e->getMessage(); }
  $db->close();
?>