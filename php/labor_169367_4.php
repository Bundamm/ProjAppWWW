<?php
  session_start();

  $nr_indeksu = '169367';  
  $nrGrupy = '4';  
  echo 'Tomasz Szewa '.$nr_indeksu.' grupa '.$nrGrupy.'<br /><br />';

  // a) Przykład include()
  echo 'Zastosowanie metody include() <br />';
  include('testinclude.php');  
  echo 'Zmienne ściągnięte metodą include(): ' .$interest. ', '.$color.'<br />';

  // Przykład require_once()
  echo 'Użycie metody require_once <br />';
  echo "Zmienna ściągnięta metodą require_once(): <br /><br />";
  require_once('testrequire.php');  
  echo '<br/><br/>';
  

  // b) Przykłady warunków if, else, elseif
  $var = 4;
  echo "Zmienna wynosi: $var <br />";
  if ($var < 5) {
      echo "Zmienna jest mniejsza niż 5.<br />";
  } elseif ($var == 5) {
      echo "Zmienna jest równa 5.<br />";
  } else {
      echo "Zmienna jest większa niż 5.<br />";
  }

  // Przykład switch
  $var2 = "niedźwiedź";
  echo "<br />Zwierz: ";
  switch ($var2) {
      case "pies":
          echo "Zmienna to pies.<br />";
          break;
      case "królik":
          echo "Zmienna to królik.<br />";
          break;
      case "kot":
          echo "Zmienna to kot.<br />";
          break;
      default:
          echo "Nie wiem co to za zwierz.<br />";
          break;
  }

  // c) Przykład pętli while() i for()
  echo "<br />Przykład pętli while:<br />";
  $i = 0;
  while ($i < 5) {
      echo "Wartość i: $i<br />";
      $i++;
  }

  echo "<br />Przykład pętli for:<br />";
  for ($j = 0; $j < 5; $j++) {
      echo "Wartość j: $j<br />";
  }

  // d) Przykłady $_GET, $_POST, $_SESSION

  // Przykład $_GET http://localhost/lab4/php/labor_169399_ISI4.php?name=Tomasz 
  echo "<br />Przykład \$_GET:<br />";
  if (isset($_GET['name'])) {
      $name = $_GET['name'];
      echo "Otrzymano z GET imię: $name<br />";
  } else {
      echo "Brak wartości 'name' w zapytaniu GET.<br />";
  }

  // Przykład $_POST
  echo "<br />Przykład zmiennej \$_POST:<br />";
  echo '<form action="labor_169367_4.php" method="POST">
            <label for="name2">Podaj imię:</label>
            <input type="text" name="name2" id="name2">
            <input type="submit" value="Wyślij">
        </form>';

  if (isset($_POST['name2'])) {
      $name2 = $_POST['name2'];
      echo "Otrzymano z POST imię: $name2<br />";
  } else {
      echo "Brak wartości 'name2' w zapytaniu POST.<br />";
  }

  // Przykład $_SESSION
  echo "<br />Przykład zmiennej \$_SESSION:<br />";
  $_SESSION['usr'] = "Tomasz";
  echo "Zmienna sesji: ".$_SESSION['usr']."<br />";
?>