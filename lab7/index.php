<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />  
    <meta http-equiv="Content-Language" content="pl" /> 
    <meta name="Author" content="Tomasz Szewa" />
    <title>Moje hobby to gry planszowe</title>
    <?php
    session_start();
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = '1';
    }


    
    if ($_GET['idp'] == '8') {
        echo '<link rel="stylesheet" href="css/style2.css">';   
    } else {
        echo '<link rel="stylesheet" href="css/style.css">';     
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedate.js" type="text/javascript"></script>
</head>
<body onload="startclock()">
<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    
?>
<header>
    <h1 id="tytul">Gry planszowe</h1>
    <nav id="navbar">
        <li>
            <ul><a href="index.php?idp=1">Strona główna</a></ul>
            <ul><a href="index.php?idp=2">Root</a></ul>
            <ul><a href="index.php?idp=3">Eclipse</a></ul>
            <ul><a href="index.php?idp=4">Oath</a></ul>
            <ul><a href="index.php?idp=5">Dune Imperium</a></ul>
            <ul><a href="index.php?idp=6">Turbo</a></ul>
            <ul><a href="index.php?idp=7">Kontakt</a></ul>
            <ul><a href="index.php?idp=8">Poligon :></a></ul>
            <ul><a href="index.php?idp=9">Wideorecenzje</a></ul>
            <ul><a href="index.php?idp=-1">Panel Admina</a></ul>
        </li>
    </nav>   
</header> 

<div class='content'>
    <?php
    
    include('cfg.php');
    include('showpage.php');
    include('admin/admin.php');
    
    $id = htmlspecialchars($_GET['idp']);

    
    static $Admin = null;

    switch($id) {
        case -1:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->LoginAdmin();
            break;
        
        case -2:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->EditPage();
            break;
        
        case -3:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->DeletePage();
            break;
        case -4:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->CreatePage();
            break;

        default:
            echo PokazStrone($id);
            break;
        
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