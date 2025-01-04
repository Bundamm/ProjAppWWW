<?php
class Shop {
    /*
     * ShowCart() - Wyświetla zawartość koszyka
     * Funkcja wyświetla wszystkie produkty dodane do koszyka,
     * umożliwia zmianę ilości produktów, ich usunięcie oraz
     * finalizację zakupu. Pokazuje również sumę całkowitą brutto.
     */
    function ShowCart() {
        global $conn;
        $totalGrossPrice = 0;
        
        // Wyświetl komunikat o sukcesie jeśli zakup został zrealizowany
        if (isset($_GET['status']) && $_GET['status'] === 'success') {
            echo '<div class="success-message">Zakup został zrealizowany pomyślnie!</div>';
            return;
        }
        
        // Obsługa akcji koszyka
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'checkout') {
                if ($this->Checkout()) {
                    header('Location: index.php?idp=-17&status=success');
                    exit();
                }
            } elseif ($action === 'clear') {
                $this->ClearCart();
                header('Location: index.php?idp=-17');
                exit();
            } elseif (isset($_POST['product_id'])) {
                $productId = intval($_POST['product_id']);
                
                if ($action === 'increase') {
                    $_SESSION['cart'][$productId]++;
                } elseif ($action === 'decrease') {
                    if ($_SESSION['cart'][$productId] > 1) {
                        $_SESSION['cart'][$productId]--;
                    } else {
                        unset($_SESSION['cart'][$productId]);
                    }
                } elseif ($action === 'remove') {
                    unset($_SESSION['cart'][$productId]);
                }
                
                header('Location: index.php?idp=-17');
                exit();
            }
        }
        
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            echo '<div class="cart-empty">Koszyk jest pusty.</div>';
            return;
        }

        echo '<div class="cart-items">';
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $query = "SELECT * FROM products WHERE id = " . intval($productId);
            $result = $conn->query($query);
            if ($product = $result->fetch_assoc()) {
                $grossPrice = $product['cena_netto'] * (1 + ($product['podatek_vat'] / 100));
                $totalGrossPrice += $grossPrice * $quantity;

                echo '<div class="cart-item">';
                echo '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" alt="' . htmlspecialchars($product['tytul']) . '" class="cart-product-image">';
                echo '<span>' . htmlspecialchars($product['tytul']) . '</span>';
                echo '<span>Cena brutto: ' . number_format($grossPrice, 2) . ' PLN</span>';
                echo '<span>Ilość: ' . $quantity . '</span>';
                echo '<form method="POST" action="index.php?idp=-17">';
                echo '<input type="hidden" name="product_id" value="' . $productId . '">';
                if ($quantity < $product['ilosc_dostepnych']) {
                    echo '<button type="submit" name="action" value="increase">+</button>';
                }
                if ($quantity > 1) {
                    echo '<button type="submit" name="action" value="decrease">-</button>';
                }
                echo '<button type="submit" name="action" value="remove">Usuń</button>';
                echo '</form>';
                echo '</div>';
            }
        }
        echo '</div>';
        
        echo '<div class="cart-total">';
        echo '<h3>Suma całkowita (brutto): ' . number_format($totalGrossPrice, 2) . ' PLN</h3>';
        echo '</div>';
        
        // Dodaj przyciski finalizacji zakupu i wyczyszczenia koszyka
        echo '<div class="cart-actions">';
        echo '<form method="POST" action="index.php?idp=-17" class="checkout-form" style="display: inline-block; margin-right: 10px;">';
        echo '<button type="submit" name="action" value="checkout" class="checkout-button">Wykonaj zakup</button>';
        echo '</form>';
        
        echo '<form method="POST" action="index.php?idp=-17" style="display: inline-block;">';
        echo '<button type="submit" name="action" value="clear" class="clear-button">Wyczyść koszyk</button>';
        echo '</form>';
        echo '</div>';
    }

    /*
     * AddToCart($productId) - Dodaje produkt do koszyka
     * Funkcja sprawdza dostępność produktu i dodaje go do koszyka
     * lub zwiększa jego ilość jeśli już się tam znajduje.
     * @param int $productId - ID produktu do dodania
     * @return bool - true jeśli dodano produkt, false w przeciwnym razie
     */
    function AddToCart($productId) {
        global $conn;
        
        // Sprawdź czy produkt istnieje i jest dostępny
        $query = "SELECT ilosc_dostepnych FROM products WHERE id = ? AND status_dostepnosci = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($product = $result->fetch_assoc()) {
            // Inicjalizuj koszyk jeśli nie istnieje
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            
            // Sprawdź czy dodanie kolejnej sztuki nie przekroczy dostępnej ilości
            $currentQuantity = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
            
            if ($currentQuantity < $product['ilosc_dostepnych']) {
                // Dodaj do koszyka lub zwiększ ilość
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]++;
                } else {
                    $_SESSION['cart'][$productId] = 1;
                }
                return true;
            }
        }
        return false;
    }

    /*
     * ShopPage() - Wyświetla stronę sklepu
     * Funkcja wyświetla listę dostępnych produktów z ich cenami,
     * opisami i przyciskami do dodania do koszyka.
     */
    function ShopPage() {
        global $conn;
        
        // Obsługa dodawania do koszyka
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['action']) && $_POST['action'] === 'add') {
            $productId = intval($_POST['product_id']);
            $this->AddToCart($productId);
            header('Location: index.php?idp=-16&status=added');
            exit();
        }
        
        // Wybierz tylko produkty z dostępną ilością
        $query = "SELECT * FROM products WHERE ilosc_dostepnych > 0 AND status_dostepnosci = 1 ORDER BY id DESC";
        $result = $conn->query($query);

        echo '<div class="product-list">';
        if ($result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                $grossPrice = $product['cena_netto'] * (1 + ($product['podatek_vat'] / 100));
                echo '<div class="product-item">';
                echo '<h3>' . htmlspecialchars($product['tytul']) . '</h3>';
                echo '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image">';
                echo '<p>' . htmlspecialchars($product['opis']) . '</p>';
                echo '<p>Cena brutto: ' . number_format($grossPrice, 2) . ' PLN</p>';
                echo '<p>Dostępna ilość: ' . $product['ilosc_dostepnych'] . '</p>';
                echo '<form method="POST" action="index.php?idp=-16">';
                echo '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
                echo '<button type="submit" name="action" value="add">Dodaj do koszyka</button>';
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<p>Brak dostępnych produktów.</p>';
        }
        echo '</div>';
        
        // Pokaż komunikat o sukcesie jeśli produkt został dodany
        if (isset($_GET['status']) && $_GET['status'] === 'added') {
            echo '<div class="success-message">Produkt został dodany do koszyka!</div>';
        }
    }

    /*
     * Checkout() - Finalizuje zakup
     * Funkcja przetwarza zakup, aktualizując ilości produktów w bazie
     * i czyszcząc koszyk po udanym zakupie. Używa transakcji do
     * zapewnienia spójności danych.
     * @return bool - true jeśli zakup się powiódł, false w przeciwnym razie
     */
    function Checkout() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return false;
        }

        global $conn;
        $success = true;

        // Rozpocznij transakcję aby zapewnić spójność wszystkich aktualizacji
        $conn->begin_transaction();

        try {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                // Pobierz aktualną ilość produktu
                $query = "SELECT ilosc_dostepnych FROM products WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($product = $result->fetch_assoc()) {
                    $newQuantity = $product['ilosc_dostepnych'] - $quantity;
                    
                    // Sprawdź czy mamy wystarczającą ilość
                    if ($newQuantity < 0) {
                        throw new Exception("Niewystarczająca ilość produktu o ID: " . $productId);
                    }

                    // Zaktualizuj ilość produktu i status jeśli potrzeba
                    $updateQuery = "UPDATE products SET 
                        ilosc_dostepnych = ?,
                        status_dostepnosci = CASE WHEN ? = 0 THEN 0 ELSE status_dostepnosci END 
                        WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("iii", $newQuantity, $newQuantity, $productId);
                    
                    if (!$updateStmt->execute()) {
                        throw new Exception("Błąd podczas aktualizacji produktu o ID: " . $productId);
                    }
                    
                    $updateStmt->close();
                }
                $stmt->close();
            }

            // Jeśli dotarliśmy tutaj, wszystkie aktualizacje się powiodły
            $conn->commit();
            // Wyczyść koszyk
            $_SESSION['cart'] = array();
            return true;

        } catch (Exception $e) {
            // Jeśli wystąpił błąd, wycofaj wszystkie zmiany
            $conn->rollback();
            echo '<div class="error-message">Błąd podczas realizacji zakupu: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    /*
     * ClearCart() - Czyści zawartość koszyka
     * Funkcja usuwa wszystkie produkty z koszyka.
     */
    function ClearCart() {
        unset($_SESSION['cart']);
        echo '<div class="success">Koszyk został wyczyszczony.</div>';
    }
}
?>