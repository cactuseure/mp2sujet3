<?php
    session_start();
    require 'bdd/MySQL.inc.php';
    $db = MySQL::getInstance();
    if ($db == null) echo "Impossible de se connecter &agrave; la base de donn&eacute;es !";
    else try 
    {
        //echo $_SESSION["idFormation"];
        //var_dump($db->getFormation("1"));$_GET['nom']
        //var_dump(strval($idFormation));
        //var_dump($db->getFormation($_GET['Formation']));
        //var_dump($db->getFormations());
        $Formation = $db->getFormation($_GET['Formation'])[0];
        //var_dump($Formation);
?>

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
        <h1> <?php echo($Formation->getTitre()); ?> </h1>
        
    </div>
</div>

<?php
  }
  catch (Exception $e){ echo $e->getMessage(); }
  $db->close();
?>


