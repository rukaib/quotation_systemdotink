/**
 * Quotation Management JavaScript
 * Handles dynamic item rows and calculations
 */

let itemCounter = 0;

// Add first row on page load
document.addEventListener('DOMContentLoaded', function() {
    addItemRow();
});

// Add item button
document.getElementById('addItemBtn').addEventListener('click', function() {
    addItemRow();
});

// Discount input listener
document.getElementById('discountInput').addEventListener('input', function() {
    calculateTotals();
});

/**
 * Add new item row
 */
function addItemRow() {
    itemCounter++;
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.id = `item-row-${itemCounter}`;
    
    row.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" 
                   name="items[${itemCounter}][name]" required>
        </td>
        <td>
            <textarea class="form-control form-control-sm" 
                      name="items[${itemCounter}][description]" rows="2"></textarea>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" 
                   name="items[${itemCounter}][warranty]" placeholder="1 Year">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-qty" 
                   name="items[${itemCounter}][quantity]" value="1" min="1" required 
                   data-row="${itemCounter}" onchange="calculateRow(${itemCounter})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-price" 
                   name="items[${itemCounter}][price]" value="0" step="0.01" min="0" required 
                   data-row="${itemCounter}" onchange="calculateRow(${itemCounter})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-vat" 
                   name="items[${itemCounter}][vat]" value="0" step="0.01" readonly>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-total" 
                   name="items[${itemCounter}][total]" value="0" step="0.01" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${itemCounter})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

/**
 * Remove item row
 */
function removeRow(rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

/**
 * Calculate single row
 */
function calculateRow(rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    if (!row) return;
    
    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    
    const lineTotal = qty * price;
    const vat = lineTotal * 0.18; // 18% VAT
    const totalWithVat = lineTotal + vat;
    
    row.querySelector('.item-vat').value = vat.toFixed(2);
    row.querySelector('.item-total').value = totalWithVat.toFixed(2);
    
    calculateTotals();
}

/**
 * Calculate all totals
 */
function calculateTotals() {
    let subtotal = 0;
    
    // Sum all line totals (without VAT)
    document.querySelectorAll('.item-total').forEach(input => {
        const totalWithVat = parseFloat(input.value) || 0;
        const vat = parseFloat(input.closest('tr').querySelector('.item-vat').value) || 0;
        const lineSubtotal = totalWithVat - vat;
        subtotal += lineSubtotal;
    });
    
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const subtotalAfterDiscount = subtotal - discount;
    const tax = subtotalAfterDiscount * 0.18; // 18% VAT
    const grandTotal = subtotalAfterDiscount + tax;
    
    // Update display
    document.getElementById('subtotalDisplay').textContent = `Rs. ${formatNumber(subtotal)}`;
    document.getElementById('taxDisplay').textContent = `Rs. ${formatNumber(tax)}`;
    document.getElementById('grandTotalDisplay').textContent = `Rs. ${formatNumber(grandTotal)}`;
    
    // Update hidden fields
    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('taxInput').value = tax.toFixed(2);
    document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Form validation before submit
 */
document.getElementById('quotationForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('#itemsTableBody tr');
    
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the quotation.');
        return false;
    }
    
    // Validate each item has required fields
    let valid = true;
    items.forEach(row => {
        const name = row.querySelector('input[name*="[name]"]').value.trim();
        const qty = row.querySelector('input[name*="[quantity]"]').value;
        const price = row.querySelector('input[name*="[price]"]').value;
        
        if (!name || qty <= 0 || price < 0) {
            valid = false;
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Please fill in all required item fields correctly.');
        return false;
    }
});