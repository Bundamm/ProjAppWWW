document.addEventListener('DOMContentLoaded', function() {
    // Znajduje wszystkie formularze zmiany ilości
    const quantityForms = document.querySelectorAll('.quantity-form');

    quantityForms.forEach(form => {
        const minusBtn = form.querySelector('.minus');
        const plusBtn = form.querySelector('.plus');
        const quantitySpan = form.querySelector('.quantity-value');
        const adjustmentInput = form.querySelector('.quantity-adjustment');
        const productId = form.querySelector('input[name="product_id"]').value;

        // Obsługa przycisku minus
        minusBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleQuantityChange(-1, productId, quantitySpan);
        });

        // Obsługa przycisku plus
        plusBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleQuantityChange(1, productId, quantitySpan);
        });
    });

    /*
     * handleQuantityChange
     * Obsługuje zmianę ilości produktu.
     * {number} adjustment - Wartość zmiany ilości (-1 lub 1).
     * {string} productId - ID produktu.
     * {HTMLElement} quantitySpan - Element wyświetlający ilość.
     */
    function handleQuantityChange(adjustment, productId, quantitySpan) {
        const currentQuantity = parseInt(quantitySpan.textContent);
        
        // Sprawdź czy nie próbujemy zejść poniżej 0
        if (currentQuantity + adjustment < 0) {
            return;
        }

        // Przygotuj dane do wysłania
        const formData = new FormData();
        formData.append('adjust_quantity', 'true');
        formData.append('product_id', productId);
        formData.append('adjustment', adjustment);

        // Wyślij żądanie AJAX
        fetch('index.php?idp=-12', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            // Aktualizuj wyświetlaną ilość
            quantitySpan.textContent = currentQuantity + adjustment;
            
            // Zaktualizuj klasę statusu dostępności jeśli potrzeba
            const row = quantitySpan.closest('tr');
            const statusSpan = row.querySelector('.status-available, .status-unavailable');
            if (statusSpan) {
                if (currentQuantity + adjustment > 0) {
                    statusSpan.className = 'status-available';
                    statusSpan.textContent = 'Dostępny';
                } else {
                    statusSpan.className = 'status-unavailable';
                    statusSpan.textContent = 'Niedostępny';
                }
            }
        })
        .catch(error => {
            console.error('Błąd podczas aktualizacji ilości:', error);
            // Przywróć poprzednią wartość w przypadku błędu
            quantitySpan.textContent = currentQuantity;
        });
    }
});
