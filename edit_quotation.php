<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    redirect('quotation_list.php');
}

// 1. Fetch the specific quotation
$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute([$id]);
$quotation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quotation) {
    setFlashMessage("Quotation not found.", 'error');
    redirect('quotation_list.php');
}

// 2. Fetch quotation items associated with this quotation
$stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY item_order ASC");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch All Customers (For the Auto-Select Search)
$custStmt = $pdo->query("SELECT * FROM customers ORDER BY company_name ASC");
$customers = $custStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Fetch All Products (For the Auto-Select Search)
$prodStmt = $pdo->query("SELECT * FROM products ORDER BY product_name ASC");
$products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quotation - DOT INK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-dark sidebar min-vh-100">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3">DOT INK</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="quotation_list.php">
                                <i class="bi bi-file-earmark-text"></i> Quotations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="create_quotation.php">
                                <i class="bi bi-plus-circle"></i> New Quotation
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Quotation</h1>
                    
                    <!-- ACTION BUTTONS: PRINT & BACK -->
                    <div>
                        <!-- PRINT BUTTON ADDED HERE -->
                        <a href="print_quotation.php?id=<?= $id ?>" target="_blank" class="btn btn-warning me-2">
                            <i class="bi bi-printer"></i> Print Preview
                        </a>
                        
                        <a href="quotation_list.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <form id="quotationForm" method="POST" action="update_quotation.php">
                    <input type="hidden" name="quotation_id" value="<?= $id ?>">
                    
                    <!-- DATA LISTS FOR SEARCHING (Hidden) -->
                    <datalist id="customerList">
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= htmlspecialchars($c['company_name']) ?>">
                                <?= htmlspecialchars($c['customer_id']) ?> | <?= htmlspecialchars($c['contact_person']) ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>

                    <datalist id="productList">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= htmlspecialchars($p['product_name']) ?>">
                                <?= htmlspecialchars($p['product_id']) ?> - Rs. <?= number_format($p['unit_price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>

                    <!-- JavaScript Data Objects (For auto-filling details) -->
                    <script>
                        const customerData = <?php echo json_encode($customers); ?>;
                        const productData = <?php echo json_encode($products); ?>;
                    </script>

                    <div class="row">
                        <!-- Left Column: Quotation Details -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Quotation Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Quotation Number</label>
                                        <input type="text" class="form-control" name="quotation_no" 
                                               value="<?= htmlspecialchars($quotation['quotation_no']) ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="quotation_date" 
                                               value="<?= htmlspecialchars($quotation['quotation_date']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Prepared By</label>
                                        <input type="text" class="form-control" name="prepared_by" 
                                               value="<?= htmlspecialchars($quotation['prepared_by']) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Customer Information -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Search Field -->
                                    <div class="mb-3">
                                        <label class="form-label">Company / Organization *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="company_name" id="searchCompany" 
                                                   list="customerList" placeholder="Type to search..." 
                                                   value="<?= htmlspecialchars($quotation['company_name']) ?>" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="fillCustomerDetails()">
                                                <i class="bi bi-search"></i> Fill
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" name="contact_person" id="contact_person" 
                                               value="<?= htmlspecialchars($quotation['contact_person']) ?>">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" id="phone" 
                                                   value="<?= htmlspecialchars($quotation['phone']) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" id="email" 
                                                   value="<?= htmlspecialchars($quotation['email']) ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" id="address" rows="2"><?= htmlspecialchars($quotation['address']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Table -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Terms & Conditions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Delivery Terms</label>
                                    <input type="text" class="form-control" name="delivery_terms" 
                                           value="<?= htmlspecialchars($quotation['delivery_terms']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Validity</label>
                                    <input type="text" class="form-control" name="validity" 
                                           value="<?= htmlspecialchars($quotation['validity']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Terms</label>
                                    <input type="text" class="form-control" name="payment_terms" 
                                           value="<?= htmlspecialchars($quotation['payment_terms']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Stock Availability</label>
                                    <input type="text" class="form-control" name="stock_availability" 
                                           value="<?= htmlspecialchars($quotation['stock_availability']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Quotation Items</h5>
                            <button type="button" class="btn btn-light btn-sm" id="addItemBtn">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%;">Item Name *</th>
                                            <th style="width: 25%;">Description</th>
                                            <th style="width: 10%;">Warranty</th>
                                            <th style="width: 8%;">Qty *</th>
                                            <th style="width: 12%;">Price *</th>
                                            <th style="width: 10%;">VAT (18%)</th>
                                            <th style="width: 10%;">Total</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <?php 
                                        $counter = 0;
                                        foreach ($items as $item): 
                                            $counter++;
                                        ?>
                                        <tr id="item-row-<?= $counter ?>">
                                            <td>
                                                <!-- DB Column: product_name -->
                                                <input type="text" class="form-control form-control-sm product-search" 
                                                       name="items[<?= $counter ?>][product_name]" 
                                                       list="productList"
                                                       value="<?= htmlspecialchars($item['product_name']) ?>" required
                                                       onchange="fillProductDetails(this, <?= $counter ?>)">
                                            </td>
                                            <td>
                                                <!-- DB Column: product_description -->
                                                <textarea class="form-control form-control-sm product-desc" 
                                                          name="items[<?= $counter ?>][product_description]" 
                                                          rows="2"><?= htmlspecialchars($item['product_description']) ?></textarea>
                                            </td>
                                            <td>
                                                <!-- DB Column: warranty -->
                                                <input type="text" class="form-control form-control-sm product-warranty" 
                                                       name="items[<?= $counter ?>][warranty]" 
                                                       value="<?= htmlspecialchars($item['warranty']) ?>">
                                            </td>
                                            <td>
                                                <!-- DB Column: quantity -->
                                                <input type="number" class="form-control form-control-sm item-qty" 
                                                       name="items[<?= $counter ?>][quantity]" 
                                                       value="<?= $item['quantity'] ?>" min="1" required 
                                                       onchange="calculateRow(<?= $counter ?>)">
                                            </td>
                                            <td>
                                                <!-- DB Column: unit_price -->
                                                <input type="number" class="form-control form-control-sm item-price" 
                                                       name="items[<?= $counter ?>][unit_price]" 
                                                       value="<?= $item['unit_price'] ?>" step="0.01" min="0" required 
                                                       onchange="calculateRow(<?= $counter ?>)">
                                            </td>
                                            <td>
                                                <!-- DB Column: vat_amount -->
                                                <input type="number" class="form-control form-control-sm item-vat" 
                                                       name="items[<?= $counter ?>][vat_amount]" 
                                                       value="<?= $item['vat_amount'] ?>" step="0.01" readonly>
                                            </td>
                                            <td>
                                                <!-- DB Column: line_total -->
                                                <input type="number" class="form-control form-control-sm item-total" 
                                                       name="items[<?= $counter ?>][line_total]" 
                                                       value="<?= $item['line_total'] ?>" step="0.01" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(<?= $counter ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Section -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Notes</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars($quotation['notes']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0">Totals</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <strong id="subtotalDisplay">Rs. <?= number_format($quotation['subtotal'], 2) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <div class="input-group input-group-sm" style="width: 150px;">
                                            <input type="number" class="form-control" name="discount" id="discountInput" 
                                                   value="<?= $quotation['discount'] ?>" step="0.01" min="0" onchange="calculateTotals()">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (18%):</span>
                                        <strong id="taxDisplay">Rs. <?= number_format($quotation['tax_amount'], 2) ?></strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h5>Grand Total:</h5>
                                        <h5 class="text-primary" id="grandTotalDisplay">Rs. <?= number_format($quotation['grand_total'], 2) ?></h5>
                                    </div>
                                    
                                    <!-- Hidden Inputs for Form Submission -->
                                    <input type="hidden" name="subtotal" id="subtotalInput" value="<?= $quotation['subtotal'] ?>">
                                    <input type="hidden" name="tax_amount" id="taxInput" value="<?= $quotation['tax_amount'] ?>">
                                    <input type="hidden" name="grand_total" id="grandTotalInput" value="<?= $quotation['grand_total'] ?>">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-save"></i> Update Quotation
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Item Counter from PHP
        let itemCounter = <?= $counter ?>;

        // --- CUSTOMER SEARCH & AUTO-FILL ---
        document.getElementById('searchCompany').addEventListener('change', fillCustomerDetails);
        document.getElementById('searchCompany').addEventListener('input', fillCustomerDetails);

        function fillCustomerDetails() {
            const searchVal = document.getElementById('searchCompany').value;
            // Find customer in the JS object
            const customer = customerData.find(c => c.company_name === searchVal);
            
            if (customer) {
                document.getElementById('contact_person').value = customer.contact_person || '';
                document.getElementById('phone').value = customer.phone || '';
                document.getElementById('email').value = customer.email || '';
                document.getElementById('address').value = customer.address || '';
            }
        }

        // --- PRODUCT SEARCH & AUTO-FILL ---
        function fillProductDetails(inputElement, rowId) {
            const searchVal = inputElement.value;
            // Find product in the JS object
            const product = productData.find(p => p.product_name === searchVal);
            
            if (product) {
                // Select inputs in this specific row
                const row = document.getElementById('item-row-' + rowId);
                row.querySelector('.product-desc').value = product.product_description || '';
                row.querySelector('.product-warranty').value = product.warranty || '';
                row.querySelector('.item-price').value = product.unit_price || 0;
                
                // Recalculate totals for this row
                calculateRow(rowId);
            }
        }

        // --- CALCULATION LOGIC ---
        function calculateRow(rowId) {
            const row = document.getElementById('item-row-' + rowId);
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            
            // Calculate
            const totalBeforeTax = qty * price;
            const vat = totalBeforeTax * 0.18; // 18% VAT
            const lineTotal = totalBeforeTax + vat;

            // Update Row Inputs
            row.querySelector('.item-vat').value = vat.toFixed(2);
            row.querySelector('.item-total').value = lineTotal.toFixed(2);

            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            // Loop through all items to sum up totals
            const rows = document.querySelectorAll('#itemsTableBody tr');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const vat = parseFloat(row.querySelector('.item-vat').value) || 0;
                
                subtotal += (qty * price);
                totalTax += vat;
            });

            // Get Discount
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            
            // Final Calculation
            const grandTotal = subtotal + totalTax - discount;

            // Update Displays
            document.getElementById('subtotalDisplay').innerText = "Rs. " + subtotal.toFixed(2);
            document.getElementById('taxDisplay').innerText = "Rs. " + totalTax.toFixed(2);
            document.getElementById('grandTotalDisplay').innerText = "Rs. " + grandTotal.toFixed(2);

            // Update Hidden Inputs
            document.getElementById('subtotalInput').value = subtotal.toFixed(2);
            document.getElementById('taxInput').value = totalTax.toFixed(2);
            document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
        }

        // --- ADD/REMOVE ROWS ---
        document.getElementById('addItemBtn').addEventListener('click', function() {
            itemCounter++;
            const tbody = document.getElementById('itemsTableBody');
            const tr = document.createElement('tr');
            tr.id = 'item-row-' + itemCounter;
            
            tr.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm product-search" 
                           name="items[${itemCounter}][product_name]" 
                           list="productList" required
                           onchange="fillProductDetails(this, ${itemCounter})">
                </td>
                <td>
                    <textarea class="form-control form-control-sm product-desc" 
                              name="items[${itemCounter}][product_description]" rows="2"></textarea>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm product-warranty" 
                           name="items[${itemCounter}][warranty]">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-qty" 
                           name="items[${itemCounter}][quantity]" value="1" min="1" required 
                           onchange="calculateRow(${itemCounter})">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-price" 
                           name="items[${itemCounter}][unit_price]" value="0.00" step="0.01" min="0" required 
                           onchange="calculateRow(${itemCounter})">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-vat" 
                           name="items[${itemCounter}][vat_amount]" value="0.00" readonly>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-total" 
                           name="items[${itemCounter}][line_total]" value="0.00" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${itemCounter})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        function removeRow(id) {
            const row = document.getElementById('item-row-' + id);
            if(row) {
                row.remove();
                calculateTotals();
            }
        }
        
        // Initial Calculation on Load
        document.addEventListener('DOMContentLoaded', calculateTotals);
    </script>
</body>
</html>