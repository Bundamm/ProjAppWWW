<?php
class Products {
    /**
     * DodajProdukt - Dodaje nowy produkt do bazy danych
     * 
     * Funkcja wyświetla formularz dodawania produktu i obsługuje jego wysłanie.
     * Zapisuje wszystkie dane produktu wraz ze zdjęciem do bazy danych.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function DodajProdukt() {
        // Tworzy nowy obiekt klasy Admin do zarządzania uprawnieniami
        $Admin = new Admin(); 
        // Sprawdza, czy użytkownik jest zalogowany jako administrator
        $status_login = $Admin->CheckLogin(); 
        if($status_login != 1) {
            // Wyświetla formularz logowania, jeśli użytkownik nie jest zalogowany
            echo $Admin->FormularzLogowania(); 
            return;
        }

        if(isset($_POST['submit'])) {
            global $conn;
            
            $tytul = mysqli_real_escape_string($conn, $_POST['tytul']);
            $opis = mysqli_real_escape_string($conn, $_POST['opis']);
            $cena_netto = number_format(floatval($_POST['cena_netto']), 2, '.', '');
            $podatek_vat = intval($_POST['podatek_vat']);
            $ilosc_dostepnych = intval($_POST['ilosc_dostepnych']);
            $status_dostepnosci = intval($_POST['status_dostepnosci']);
            $kategoria = intval($_POST['kategoria']);
            $gabaryt_produktu = intval($_POST['gabaryt_produktu']);
            $data_wygasniecia = date('Y-m-d H:i:s', strtotime($_POST['data_wygasniecia']));
            
            // Obsługa przesyłania zdjęcia
            $zdjecie = null;
            if(isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                $zdjecie = file_get_contents($_FILES['zdjecie']['tmp_name']);
            }
            
            $query = "INSERT INTO products (tytul, opis, data_utworzenia, data_modyfikacji, data_wygasniecia, 
                     cena_netto, podatek_vat, ilosc_dostepnych, status_dostepnosci, kategoria, 
                     gabaryt_produktu, zdjecie) VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssdiiiiib", $tytul, $opis, $data_wygasniecia, $cena_netto, 
                            $podatek_vat, $ilosc_dostepnych, $status_dostepnosci, $kategoria, 
                            $gabaryt_produktu, $zdjecie);
            
            if($stmt->execute()) {
                echo '<div class="success">Produkt został dodany pomyślnie.</div>';
            } else {
                echo '<div class="error">Błąd podczas dodawania produktu: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }

        // Wyświetlanie formularza
        echo '<div class="product-form-container">
    <div class="form-header">
        <h3>Dodaj nowy produkt</h3>
    </div>
    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="form-group"><label>Tytuł:</label><input type="text" name="tytul" maxlength="255" required></div>
        <div class="form-group"><label>Opis:</label><textarea name="opis" required></textarea></div>
        <div class="form-group"><label>Cena netto:</label><input type="number" step="0.01" name="cena_netto" required></div>
        <div class="form-group"><label>VAT (%):</label><input type="number" name="podatek_vat" required></div>
        <div class="form-group"><label>Ilość:</label><input type="number" name="ilosc_dostepnych" required></div>
        <div class="form-group"><label>Status dostępności:</label>
            <select name="status_dostepnosci">
                <option value="1">Dostępny</option>
                <option value="0">Niedostępny</option>
            </select>
        </div>
        <div class="form-group"><label>Kategoria:</label>
            <select name="kategoria">';
        $this->WyswietlKategorie(); // Wywołuje metodę do wyświetlenia kategorii
        echo '
        </select></div>
        <div class="form-group"><label>Gabaryt:</label>
        <select name="gabaryt_produktu">
            <option value="1">Mały</option>
            <option value="2">Średni</option>
            <option value="3">Duży</option>
        </select></div>
        <div class="form-group"><label>Data wygaśnięcia:</label><input type="datetime-local" name="data_wygasniecia" required></div>
        <div class="form-group"><label>Zdjęcie:</label><input type="file" name="zdjecie" accept="image/*"></div>
        <div class="form-actions">
            <input type="submit" name="submit" value="Dodaj produkt" class="button add">
            <a href="?idp=-12" class="button cancel">Anuluj</a>
        </div>
        </form>
        </div>';
    }

    /**
     * UsunProdukt - Usuwa produkt z bazy danych
     * 
     * Funkcja usuwa wybrany produkt po potwierdzeniu przez użytkownika.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function UsunProdukt() {
        // Tworzy nowy obiekt klasy Admin do weryfikacji uprawnień
        $Admin = new Admin();
        // Sprawdza uprawnienia administratora
        $status_login = $Admin->CheckLogin();
        if($status_login != 1) {
            // Wyświetla formularz logowania dla niezalogowanych użytkowników
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        
        // Pobierz ID produktu z URL
        $id = isset($_GET['id']) ? intval(substr($_GET['id'], 0)) : 0;
        
        if($id > 0) {
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if($stmt->execute()) {
                echo '<div class="success">Produkt został usunięty.</div>';
            } else {
                echo '<div class="error">Błąd podczas usuwania produktu: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        
        // Przekierowanie do listy produktów
        header("Location: index.php?idp=-12");
        exit();
    }

    /**
     * EdytujProdukt - Edytuje istniejący produkt
     * 
     * Funkcja wyświetla formularz edycji produktu i obsługuje jego aktualizację.
     * Zapisuje zmiany w danych produktu do bazy danych.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function EdytujProdukt() {
        // Tworzy nowy obiekt klasy Admin do zarządzania uprawnieniami
        $Admin = new Admin(); 
        // Weryfikuje czy użytkownik ma uprawnienia administratora
        $status_login = $Admin->CheckLogin(); 
        if($status_login != 1) {
            // Wyświetla formularz logowania dla nieuprawnionych użytkowników
            echo $Admin->FormularzLogowania(); 
            return;
        }

        global $conn;
        
        // Sprawdza, czy formularz aktualizacji został wysłany
        if(isset($_POST['update'])) {
            // Pobiera i przetwarza dane z formularza
            $id = intval($_POST['id']);
            $tytul = mysqli_real_escape_string($conn, $_POST['tytul']);
            $opis = mysqli_real_escape_string($conn, $_POST['opis']);
            $cena_netto = floatval($_POST['cena_netto']);
            $podatek_vat = intval($_POST['podatek_vat']);
            $ilosc_dostepnych = intval($_POST['ilosc_dostepnych']);
            $status_dostepnosci = intval($_POST['status_dostepnosci']);
            $kategoria = intval($_POST['kategoria']);
            $gabaryt_produktu = intval($_POST['gabaryt_produktu']);
            $data_wygasniecia = date('Y-m-d H:i:s', strtotime($_POST['data_wygasniecia']));

            // Sprawdzenie czy przesłano nowe zdjęcie
            $hasNewImage = isset($_FILES['zdjecie']) && 
                          $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK && 
                          !empty($_FILES['zdjecie']['tmp_name']);

            // Przygotowanie podstawowego zapytania SQL
            $query = "UPDATE products SET 
                     tytul=?, 
                     opis=?, 
                     data_modyfikacji=NOW(), 
                     data_wygasniecia=?, 
                     cena_netto=?, 
                     podatek_vat=?, 
                     ilosc_dostepnych=?, 
                     status_dostepnosci=?, 
                     kategoria=?, 
                     gabaryt_produktu=?";

            // Inicjalizacja parametrów
            $params = [
                $tytul, 
                $opis, 
                $data_wygasniecia, 
                $cena_netto, 
                $podatek_vat, 
                $ilosc_dostepnych, 
                $status_dostepnosci, 
                $kategoria, 
                $gabaryt_produktu
            ];
            $types = "sssdiiiii";

            // Dodanie obsługi zdjęcia do zapytania
            if ($hasNewImage) {
                try {
                    // Sprawdzenie typu pliku
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($_FILES['zdjecie']['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception('Niedozwolony typ pliku. Dozwolone są tylko obrazy JPEG, PNG i GIF.');
                    }

                    // Sprawdzenie rozmiaru pliku (max 5MB)
                    if ($_FILES['zdjecie']['size'] > 5 * 1024 * 1024) {
                        throw new Exception('Plik jest zbyt duży. Maksymalny rozmiar to 5MB.');
                    }

                    // Konwersja obrazu do formatu JPEG
                    $sourceImage = imagecreatefromstring(file_get_contents($_FILES['zdjecie']['tmp_name']));
                    if ($sourceImage === false) {
                        throw new Exception('Nie udało się przetworzyć obrazu.');
                    }

                    // Tworzenie bufora dla JPEG
                    ob_start();
                    imagejpeg($sourceImage, null, 85); // 85 to jakość kompresji
                    $imageData = ob_get_contents();
                    ob_end_clean();
                    
                    imagedestroy($sourceImage);

                    // Dodanie zdjęcia do zapytania
                    $query .= ", zdjecie=?";
                    $params[] = $imageData;
                    $types .= "b";
                } catch (Exception $e) {
                    echo '<div class="error">Błąd podczas przetwarzania zdjęcia: ' . $e->getMessage() . '</div>';
                    return;
                }
            }

            // Dodanie warunku WHERE
            $query .= " WHERE id=?";
            $params[] = $id;
            $types .= "i";

            // Przygotowanie i wykonanie zapytania
            try {
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Błąd przygotowania zapytania: " . $conn->error);
                }

                $stmt->bind_param($types, ...$params);
                
                if (!$stmt->execute()) {
                    throw new Exception("Błąd wykonania zapytania: " . $stmt->error);
                }

                // Sprawdzenie czy aktualizacja się powiodła
                if ($stmt->affected_rows > 0 || !$hasNewImage) {
                    echo '<div class="success">Produkt został zaktualizowany pomyślnie.</div>';
                    // Przekierowanie po krótkim opóźnieniu
                    header("refresh:2;url=index.php?idp=-12");
                } else {
                    echo '<div class="warning">Nie wprowadzono żadnych zmian.</div>';
                }

                $stmt->close();
            } catch (Exception $e) {
                echo '<div class="error">Wystąpił błąd: ' . $e->getMessage() . '</div>';
            }
        }

        // Pobierz ID produktu z URL
        $id = isset($_GET['id']) ? intval(substr($_GET['id'], 0)) : 0;
        if($id > 0) {
            $query = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($product = $result->fetch_assoc()) {
                echo '
                <div class="edit-container">
                    <h3 class="edit-title">Edytuj produkt</h3>
                    <form method="POST" action="index.php?idp=-12" enctype="multipart/form-data" class="product-form">
                        <input type="hidden" name="id" value="' . $product['id'] . '">
                        <input type="hidden" name="update" value="1">
                        <div class="form-group">
                            <label for="tytul">Tytuł:</label>
                            <input type="text" id="tytul" name="tytul" value="' . htmlspecialchars($product['tytul']) . '" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label for="opis">Opis:</label>
                            <textarea id="opis" name="opis" required>' . htmlspecialchars($product['opis']) . '</textarea>
                        </div>
                        <div class="form-group">
                            <label for="cena_netto">Cena netto:</label>
                            <input type="number" id="cena_netto" step="0.01" name="cena_netto" value="' . $product['cena_netto'] . '" required>
                        </div>
                        <div class="form-group">
                            <label for="podatek_vat">VAT (%):</label>
                            <input type="number" id="podatek_vat" name="podatek_vat" value="' . $product['podatek_vat'] . '" required>
                        </div>
                        <div class="form-group">
                            <label for="ilosc_dostepnych">Ilość:</label>
                            <input type="number" id="ilosc_dostepnych" name="ilosc_dostepnych" value="' . $product['ilosc_dostepnych'] . '" required>
                        </div>
                        <div class="form-group">
                            <label for="status_dostepnosci">Status dostępności:</label>
                            <select id="status_dostepnosci" name="status_dostepnosci">
                                <option value="1"' . ($product['status_dostepnosci'] == 1 ? ' selected' : '') . '>Dostępny</option>
                                <option value="0"' . ($product['status_dostepnosci'] == 0 ? ' selected' : '') . '>Niedostępny</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kategoria">Kategoria:</label>
                            <select id="kategoria" name="kategoria">
                ';
                $this->WyswietlKategorie($product['kategoria']);
                echo '</select>
                </div>
                <div class="form-group">
                    <label for="gabaryt_produktu">Gabaryt:</label>
                    <select id="gabaryt_produktu" name="gabaryt_produktu">
                        <option value="1"' . ($product['gabaryt_produktu'] == 1 ? ' selected' : '') . '>Mały</option>
                        <option value="2"' . ($product['gabaryt_produktu'] == 2 ? ' selected' : '') . '>Średni</option>
                        <option value="3"' . ($product['gabaryt_produktu'] == 3 ? ' selected' : '') . '>Duży</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="data_wygasniecia">Data wygaśnięcia:</label>
                    <input type="datetime-local" id="data_wygasniecia" name="data_wygasniecia" value="' . 
                    date('Y-m-d\TH:i', strtotime($product['data_wygasniecia'])) . '" required>
                </div>
                <div class="form-group">
                    <label>Aktualne zdjęcie:</label>';
                if($product['zdjecie']) {
                    echo '<div class="current-image"><img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" style="max-width:200px;"></div>';
                }
                echo '
                </div>
                <div class="form-group">
                    <label for="zdjecie">Nowe zdjęcie:</label>
                    <input type="file" id="zdjecie" name="zdjecie" accept="image/*">
                </div>
                <div class="form-actions">
                    <input type="submit" name="update" value="Aktualizuj produkt" class="button edit">
                    <a href="?idp=-12" class="button cancel">Anuluj</a>
                </div>
            </form>
        </div>';
            } else {
                echo '<div class="error">Nie znaleziono produktu.</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="error">Nieprawidłowe ID produktu.</div>';
        }
    }

    /**
     * WyswietlKategorie - Wyświetla listę kategorii w formie opcji dla selecta
     */
    private function WyswietlKategorie($selected_id = null) {
        global $conn;
        $query = "SELECT id, nazwa FROM categories ORDER BY nazwa";
        $result = $conn->query($query);
        while($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '"' . 
                 ($selected_id == $row['id'] ? ' selected' : '') . '>' . 
                 htmlspecialchars($row['nazwa']) . '</option>';
        }
    }

    /**
     * ListaProduktow - Generuje i zwraca listę wszystkich produktów
     * 
     * Wyświetla produkty w formie tabeli z możliwością edycji i usuwania.
     * Pokazuje zdjęcia, szczegóły produktów oraz przyciski akcji.
     */
    function ListaProduktow() {
        global $conn;
        
        // Sprawdzanie czy użytkownik chce dostosować ilość produktu
        if(isset($_POST['adjust_quantity'])) {
            // Pobieranie i konwertowanie danych z formularza
            $id = intval($_POST['product_id']);
            $adjustment = intval($_POST['adjustment']);
            
            // Przygotowywanie zapytania SQL do aktualizacji ilości produktu
            $query = "UPDATE products SET ilosc_dostepnych = ilosc_dostepnych + ?, data_modyfikacji = NOW() 
                     WHERE id = ? AND (ilosc_dostepnych + ?) >= 0";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $adjustment, $id, $adjustment);
            
            // Wykonywanie zapytania i obsługa przekierowania
            if($stmt->execute()) {
                if(!isset($_POST['ajax'])) {
                    // Przekierowywanie użytkownika po pomyślnej aktualizacji
                    header("Location: index.php?idp=-12");
                    exit;
                }
            }
            $stmt->close();
            
            // Kończenie skryptu dla żądań AJAX
            if(isset($_POST['ajax'])) {
                exit;
            }
        }
        
        // Dodawanie skryptu JavaScript i rozpoczynanie kontenera listy produktów
        $output = '<script src="js/product-quantity.js"></script>';
        $output .= '<div class="product-list-container">';
        
        // Przygotowywanie zapytania SQL do pobrania produktów wraz z nazwami kategorii
        $query = "SELECT p.*, c.nazwa as nazwa_kategorii 
                 FROM products p 
                 LEFT JOIN categories c ON p.kategoria = c.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = $conn->query($query);

        // Tworzenie nagłówka tabeli produktów
        $output .= '<table class="product-table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Zdjęcie</th>';
        $output .= '<th>Tytuł</th>';
        $output .= '<th>Opis</th>';
        $output .= '<th>Cena netto</th>';
        $output .= '<th>VAT</th>';
        $output .= '<th>Ilość</th>';
        $output .= '<th>Kategoria</th>';
        $output .= '<th>Status</th>';
        $output .= '<th>Akcje</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        // Iterowanie przez wszystkie produkty i tworzenie wierszy tabeli
        while($product = $result->fetch_assoc()) {
            // Sprawdzanie dostępności produktu
            $dostepnosc = $this->SprawdzDostepnosc($product);
            
            // Rozpoczynanie nowego wiersza tabeli
            $output .= '<tr>';
            // Dodawanie komórki ze zdjęciem produktu
            $output .= '<td class="product-image-cell">';
            if($product['zdjecie']) {
                $output .= '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . 
                          '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image-small">';
            }
            $output .= '</td>';
            
            // Dodawanie pozostałych danych produktu
            $output .= '<td>' . htmlspecialchars($product['tytul']) . '</td>';
            $output .= '<td class="product-description">' . htmlspecialchars($product['opis']) . '</td>';
            $output .= '<td>' . number_format($product['cena_netto'], 2) . ' PLN</td>';
            $output .= '<td>' . $product['podatek_vat'] . '%</td>';
            
            // Dodawanie formularza do zmiany ilości produktu
            $output .= '<td class="quantity-cell">';
            $output .= '<form method="POST" class="quantity-form" onsubmit="return false;">';
            $output .= '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
            $output .= '<button type="button" class="quantity-btn minus">-</button>';
            $output .= '<span class="quantity-value">' . $product['ilosc_dostepnych'] . '</span>';
            $output .= '<button type="button" class="quantity-btn plus">+</button>';
            $output .= '<input type="hidden" name="adjustment" value="0" class="quantity-adjustment">';
            $output .= '</form>';
            $output .= '</td>';
            
            // Dodawanie komórki z nazwą kategorii
            $output .= '<td class="category-cell">';
            $output .= '<span class="category-name">' . 
                      (empty($product['nazwa_kategorii']) ? 'Brak kategorii' : htmlspecialchars($product['nazwa_kategorii'])) . 
                      '</span>';
            $output .= '</td>';
            
            // Dodawanie komórki ze statusem dostępności
            $output .= '<td><span class="status-' . ($dostepnosc ? 'available' : 'unavailable') . 
                      '">' . ($product['status_dostepnosci'] == 1 ? 'Dostępny' : 'Niedostępny') . '</span></td>';
            
            // Dodawanie przycisków akcji (edycja, usunięcie)
            $output .= '<td class="action-buttons">';
            $output .= '<a href="?idp=-14&id=' . $product['id'] . '" class="button edit">Edytuj</a> ';
            $output .= '<a href="?idp=-15&id=' . $product['id'] . '" class="button delete" onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>';
            $output .= '</td>';
            
            // Kończenie wiersza tabeli
            $output .= '</tr>';
        }
        
        // Kończenie tabeli
        $output .= '</tbody>';
        $output .= '</table>';
        
        // Dodawanie przycisku do tworzenia nowego produktu
        $output .= '<div class="add-product-button">';
        $output .= '<a href="?idp=-13" class="button add">Dodaj nowy produkt</a>';
        $output .= '</div>';
        
        // Dodawanie skryptu JavaScript do obsługi przycisków zmiany ilości
        $output .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const quantityForms = document.querySelectorAll(".quantity-form");
                quantityForms.forEach(form => {
                    const minusBtn = form.querySelector(".minus");
                    const plusBtn = form.querySelector(".plus");
                    const adjustmentInput = form.querySelector(".quantity-adjustment");
                    
                    minusBtn.addEventListener("click", function(e) {
                        adjustmentInput.value = -1;
                    });
                    
                    plusBtn.addEventListener("click", function(e) {
                        adjustmentInput.value = 1;
                    });
                });
            });
        </script>';
        
        // Kończenie kontenera listy produktów
        $output .= '</div>';
        return $output;
    }

    /**
     * SprawdzDostepnosc - Sprawdza dostępność produktu
     * 
     * Sprawdza czy produkt jest dostępny na podstawie:
     * - statusu dostępności
     * - ilości dostępnych sztuk
     * - daty wygaśnięcia
     */
    private function SprawdzDostepnosc($product) {
        $current_date = date('Y-m-d H:i:s');
        return ($product['status_dostepnosci'] == 1 && 
                $product['ilosc_dostepnych'] > 0 && 
                $product['data_wygasniecia'] > $current_date);
    }

    /**
     * PokazProdukty - Wyświetla panel zarządzania produktami
     * 
     * Główna funkcja wyświetlająca panel produktów.
     * Obsługuje różne akcje (dodawanie, edycja, usuwanie)
     * i wyświetla odpowiednie formularze lub listę produktów.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function PokazProdukty() {
        // Tworzy nowy obiekt klasy Admin do zarządzania uprawnieniami
        $Admin = new Admin();
        // Sprawdza czy użytkownik jest zalogowany jako administrator
        $status_login = $Admin->CheckLogin();
        if($status_login != 1) {
            // Wyświetla formularz logowania dla niezalogowanych użytkowników
            echo $Admin->FormularzLogowania();
            return;
        }
        
        echo '<h3 class="h3-admin">Panel Produktów</h3>';
        
        echo '<a class="return-btn" href="?idp=-1">Powrót do Panelu Admina</a>';
        
        
        // Obsługa różnych akcji
        // Sprawdza, czy została określona akcja w parametrach GET
        if(isset($_GET['action'])) {
            // Wybiera odpowiednią operację na podstawie wartości akcji
            switch($_GET['action']) {
                case 'add':
                    // Wywołuje metodę dodawania nowego produktu
                    $this->DodajProdukt();
                    break;
                case 'edit':
                    // Wywołuje metodę edycji istniejącego produktu
                    $this->EdytujProdukt();
                    break;
                case 'delete':
                    // Wywołuje metodę usuwania produktu
                    $this->UsunProdukt();
                    break;
                default:
                    // W przypadku nieznanej akcji wyświetla listę produktów
                    echo $this->ListaProduktow();
            }
        } else {
            // Jeśli nie określono akcji, wyświetla domyślną listę produktów
            echo $this->ListaProduktow();
        }
    }
}
?>