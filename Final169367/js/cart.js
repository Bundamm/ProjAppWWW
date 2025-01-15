document.addEventListener('DOMContentLoaded', function() {
    // Funkcja do aktualizacji koszyka
    function updateCart(productId, action) {
        // Tworzenie obiektu FormData do przesłania danych
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', action);

        // Wysyłanie żądania AJAX do serwera
        fetch('index.php?idp=-17', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Znajdowanie kontenera koszyka
            const cartContainer = document.querySelector('.cart-items');
            if (cartContainer) {
                // Tworzenie tymczasowego elementu div do parsowania HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Aktualizacja zawartości koszyka
                const newCartItems = tempDiv.querySelector('.cart-items');
                if (newCartItems) {
                    cartContainer.innerHTML = newCartItems.innerHTML;
                } else if (tempDiv.querySelector('.cart-empty')) {
                    // Wyświetlanie komunikatu o pustym koszyku
                    cartContainer.innerHTML = tempDiv.querySelector('.cart-empty').outerHTML;
                }
                
                // Ponowne dodawanie event listenerów do zaktualizowanych przycisków
                attachCartEventListeners();
            }
        })
        .catch(error => {
            console.error('Błąd podczas aktualizacji koszyka:', error);
        });
    }

    // Funkcja do dodawania event listenerów do przycisków w koszyku
    function attachCartEventListeners() {
        // Obsługa przycisków zwiększania ilości
        document.querySelectorAll('button[value="increase"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'increase');
            };
        });

        // Obsługa przycisków zmniejszania ilości
        document.querySelectorAll('button[value="decrease"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'decrease');
            };
        });

        // Obsługa przycisków usuwania produktu
        document.querySelectorAll('button[value="remove"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'remove');
            };
        });

        // Obsługa przycisku czyszczenia koszyka
        const clearCartButton = document.querySelector('button[value="clear"]');
        if (clearCartButton) {
            clearCartButton.onclick = function(e) {
                e.preventDefault();
                updateCart(null, 'clear');
            };
        }

        // Obsługa przycisku finalizacji zamówienia
        const checkoutButton = document.querySelector('button[value="checkout"]');
        if (checkoutButton) {
            checkoutButton.onclick = function(e) {
                e.preventDefault();
                updateCart(null, 'checkout');
            };
        }
    }

    // Inicjalizacja event listenerów przy załadowaniu strony
    attachCartEventListeners();
});
