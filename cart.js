document.addEventListener("DOMContentLoaded", function() {

    const cartTable = document.querySelector('.cart-table');
    const totalPointsUsedEl = document.getElementById('total-points-used');

    // Update the total points column for a row
    function updateRowTotal(row) {
        const qty = parseInt(row.querySelector('input[type="text"]').value);
        const points = parseInt(row.querySelector('td:nth-child(4)').textContent.replace(/,/g, ''));
        row.querySelector('.total-points').textContent = qty * points;
    }

    // Recalculate total points for all selected items
    function recalcTotalPoints() {
        let total = 0;
        cartTable.querySelectorAll('tbody tr').forEach(row => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            if(checkbox.checked) {
                const qty = parseInt(row.querySelector('input[type="text"]').value);
                const points = parseInt(row.querySelector('td:nth-child(4)').textContent.replace(/,/g, ''));
                total += qty * points;
            }
        });
        totalPointsUsedEl.textContent = 'Total Points: ' + total;
    }

    // Select all checkboxes
    const selectAllCheckbox = document.getElementById('select-all');
    selectAllCheckbox?.addEventListener('change', function() {
        cartTable.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = this.checked);
        recalcTotalPoints();
    });

    // Handle increase button
    cartTable.querySelectorAll('.btn-increase').forEach(btn => {
        
        btn.addEventListener('click', function() {
            
            const row = this.closest('tr');
            const input = row.querySelector('input[type="text"]');
            const voucherId = row.dataset.voucherId;

            fetch('cart_controller.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'increase', voucher_id: voucherId, quantity: 1})
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    input.value = data.updated_quantity;
                    updateRowTotal(row);
                    recalcTotalPoints();
                }
            });
        });
    });

    // Handle decrease button
    cartTable.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('input[type="text"]');
            const voucherId = row.dataset.voucherId;

            fetch('cart_controller.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'decrease', voucher_id: voucherId, quantity: 1})
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    if(data.deleted) {
                        row.remove();
                    } else {
                        input.value = data.updated_quantity;
                        updateRowTotal(row);
                    }
                    recalcTotalPoints();
                }
            });
        });
    });

    // Handle delete button
    cartTable.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            fetch('cart_controller.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'delete', cart_id: row.dataset.cartId})
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success' && data.deleted) {
                    row.remove();
                    recalcTotalPoints();
                }
            });
        });
    });

    // Recalculate total points when checkboxes change
    cartTable.querySelectorAll('input[name="selected[]"]').forEach(cb => {
        cb.addEventListener('change', recalcTotalPoints);
    });

    // Checkout button
    document.getElementById('checkout-btn')?.addEventListener('click', function() {
        const selected = Array.from(cartTable.querySelectorAll('input[name="selected[]"]:checked')).map(cb => cb.value);
        if(selected.length === 0) { 
            alert('Select at least one item to checkout!'); 
            return; 
        }
        alert('Proceed to checkout with items: ' + selected.join(', '));
    });

    // Initial calculation
    recalcTotalPoints();
});