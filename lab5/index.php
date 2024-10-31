<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />  
    <meta http-equiv="Content-Language" content="pl" /> 
    <meta name="Author" content="Tomasz Szewa" />
    <title>Moje hobby to gry planszowe</title>
    <?php
    if ($_GET['idp'] == 'poligon') {
        echo '<link rel="stylesheet" href="/css/style2.css">';   
    } else {
        echo '<link rel="stylesheet" href="/css/style.css">';     
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedate.js" type="text/javascript"></script>
</head>
<body onload="startclock()">
<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    if ($_GET['idp']=='') $strona='html/glowna.html';
    elseif ($_GET['idp'] =='glowna') $strona = 'html/glowna.html';
    elseif ($_GET['idp'] =='Root') $strona = 'html/Root.html';
    elseif ($_GET['idp'] =='Eclipse') $strona = 'html/Eclipse.html';
    elseif ($_GET['idp'] =='Oath') $strona = 'html/Oath.html';
    elseif ($_GET['idp'] =='Dune') $strona = 'html/Dune.html';
    elseif ($_GET['idp'] =='Heat') $strona = 'html/Turbo.html';
    elseif ($_GET['idp'] =='Kontakt') $strona = 'html/Kontakt.html';
    elseif ($_GET['idp'] =='poligon') $strona = 'html/poligon.html';
    elseif ($_GET['idp'] =='filmy') $strona = 'html/filmy.html';
    
?>
<header>
    <h1 id="tytul">Gry planszowe</h1>
    <nav id="navbar">
        <li>
            <ul><a href="index.php?idp=">Strona główna</a></ul>
            <ul><a href="index.php?idp=Root">Root</a></ul>
            <ul><a href="index.php?idp=Eclipse">Eclipse</a></ul>
            <ul><a href="index.php?idp=Oath">Oath</a></ul>
            <ul><a href="index.php?idp=Dune">Dune Imperium</a></ul>
            <ul><a href="index.php?idp=Heat">Turbo</a></ul>
            <ul><a href="index.php?idp=Kontakt">Kontakt</a></ul>
            <ul><a href="index.php?idp=poligon">Poligon :></a></ul>
            <ul><a href="index.php?idp=filmy">Wideorecenzje</a></ul>
        </li>
    </nav>   
</header> 

<div class='content'>
    <?php
    if(file_exists($strona)){
        include($strona);
    } else {
        echo "<p>Podstrona nie istnieje.</p>";
    }
    ?>
</div>

<footer>

        <?php
        $nr_indeksu = '169367';
        $nrGrupy = '4';

        echo 'Autor: Tomasz Szewa '.$nr_indeksu.' grupa '.$nrGrupy.'<br/><br/>';
        ?>

</footer>
</body>
</html>