<?php
    session_start();
?>


<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formation l'ULHN</title>
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<section id="accueil">

    <div id="myNav" class="overlay">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="overlay-content">
            <a href="formations.php">Formation</a>
            <a href="#">Litis</a>
            <a href="#">Stage</a>
            <a href="#">Profil</a>
        </div>
    </div>

    <div id="aligncenter"><img id="logo_menu" src="/mp2sujet3/assets/img/logo_ulhn_menu.png" alt="logo_iut_" onclick="openNav()"/></div>

</section>

<script>
    function openNav() {
        document.getElementById("myNav").style.width = "100%";
    }

    function closeNav() {
        document.getElementById("myNav").style.width = "0%";
    }
</script>