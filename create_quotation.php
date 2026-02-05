<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$quotationNo = generateQuotationNumber($pdo);
$currentDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quotation - DOT INK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, .8);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, .1);
            color: #fff;
        }

        /* Autocomplete */
        .autocomplete-wrapper { position: relative; }
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 280px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            display: none;
        }
        .autocomplete-dropdown.show { display: block; }
        .autocomplete-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .autocomplete-item:hover { background: #e8f4fc; }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item .item-id {
            background: #0070C0;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 8px;
        }
        .autocomplete-item .main-text { font-weight: 600; color: #333; }
        .autocomplete-item .sub-text { font-size: 12px; color: #666; margin-top: 3px; }
        .autocomplete-item .price-badge {
            background: #28a745;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            float: right;
        }
        .new-badge {
            background: #28a745;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 8px;
        }
        .loading-text { padding: 12px; text-align: center; color: #666; }

        .product-autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 450px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 0 0 6px 6px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            display: none;
        }
        .product-autocomplete-dropdown.show { display: block; }

        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        #itemsTable input, #itemsTable textarea { font-size: 12px; }

        /* VAT Toggle Styling */
        .vat-toggle-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .vat-toggle-box .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .vat-toggle-box label {
            font-weight: 600;
            font-size: 14px;
        }

        /* Hide VAT column when disabled */
        .vat-hidden .vat-column { display: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3"><i class="bi bi-printer"></i> DOT INK</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link text-white" href="quotation_list.php"><i class="bi bi-file-earmark-text"></i> Quotations</a></li>
                        <li><a class="nav-link active text-white" href="create_quotation.php"><i class="bi bi-plus-circle"></i> New Quotation</a></li>
                        <li><a class="nav-link text-white" href="customers.php"><i class="bi bi-people"></i> Customers</a></li>
                        <li><a class="nav-link text-white" href="products.php"><i class="bi bi-box"></i> Products</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-plus"></i> Create New Quotation</h1>
                    <a href="quotation_list.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                </div>

                <?php displayFlashMessage(); ?>

                <form id="quotationForm" method="POST" action="save_quotation.php">
                    <div class="row">
                        <!-- Quotation Details -->
                        <div class="col-md-5">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bi bi-file-text"></i> Quotation Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Quotation No</label>
                                            <input type="text" class="form-control bg-light" name="quotation_no" 
                                                   value="<?= $quotationNo ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" name="quotation_date" 
                                                   value="<?= $currentDate ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Prepared By</label>
                                        <input type="text" class="form-control" name="prepared_by" value="M.ASHAN" required>
                                    </div>

                                    <!-- VAT TOGGLE -->
                                    <div class="vat-toggle-box">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="vatEnabled" 
                                                   name="vat_enabled" value="1" checked>
                                            <label class="form-check-label" for="vatEnabled">
                                                <i class="bi bi-receipt"></i> Include VAT (18%)
                                            </label>
                                        </div>
                                        <small class="text-muted">Uncheck if this quotation is VAT exempt</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="col-md-7">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-building"></i> Company Information</h6>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="customer_ref_id" id="customer_ref_id" value="">
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Customer ID</label>
                                            <input type="text" class="form-control bg-light" name="customer_id" 
                                                   id="customer_id" placeholder="Auto" readonly>
                                        </div>
                                        <div class="col-md-9 mb-3">
                                            <label class="form-label">Company Name * <small class="text-muted">(Type to search)</small></label>
                                            <div class="autocomplete-wrapper">
                                                <input type="text" class="form-control" name="company_name" 
                                                       id="company_name" required autocomplete="off"
                                                       placeholder="Type company name or ID...">
                                                <div class="autocomplete-dropdown" id="customerDropdown"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Contact Person</label>
                                            <input type="text" class="form-control" name="contact_person" id="contact_person">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" id="phone">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" id="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="address" id="address">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="bi bi-card-checklist"></i> Terms & Conditions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Delivery</label>
                                    <input type="text" class="form-control" name="delivery_terms" 
                                           value="Within 2 Days after Confirmation">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Validity</label>
                                    <input type="text" class="form-control" name="validity" value="100 Days">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Payments</label>
                                    <input type="text" class="form-control" name="payment_terms" value="30 Days Credit / COD">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Stock</label>
                                    <input type="text" class="form-control" name="stock_availability" value="Available">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-cart3"></i> Quotation Items</h6>
                            <button type="button" class="btn btn-light btn-sm" id="addItemBtn">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:90px;">Product ID</th>
                                            <th style="width:24%;">Product Name *</th>
                                            <th style="width:18%;">Description</th>
                                            <th style="width:80px;">Warranty</th>
                                            <th style="width:60px;">Qty *</th>
                                            <th style="width:100px;">Price *</th>
                                            <th style="width:90px;" class="vat-column">VAT 18%</th>
                                            <th style="width:100px;">Total</th>
                                            <th style="width:35px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header"><h6 class="mb-0"><i class="bi bi-sticky"></i> Notes</h6></div>
                                <div class="card-body">
                                    <textarea class="form-control" name="notes" rows="4">
Price is negotiable after discussions.
One to one replacement warranty for manufacture faults only for Toners and Ribbons.
On-site support is included.
Cheque should be drawn in favor of DOT INK (PVT) LTD.</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0"><i class="bi bi-calculator"></i> Totals</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <strong id="subtotalDisplay">Rs. 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 align-items-center">
                                        <span>Discount:</span>
                                        <div class="input-group" style="width: 140px;">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" class="form-control" name="discount" 
                                                   id="discountInput" value="0" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 vat-row">
                                        <span>VAT (18%):</span>
                                        <strong id="taxDisplay">Rs. 0.00</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <h5>Grand Total:</h5>
                                        <h5 class="text-primary" id="grandTotalDisplay">Rs. 0.00</h5>
                                    </div>
                                    <input type="hidden" name="subtotal" id="subtotalInput">
                                    <input type="hidden" name="tax_amount" id="taxInput">
                                    <input type="hidden" name="grand_total" id="grandTotalInput">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                                <i class="bi bi-save"></i> Save Quotation
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * VAT TOGGLE FUNCTIONALITY
         */
        const vatCheckbox = document.getElementById('vatEnabled');
        const itemsTable = document.getElementById('itemsTable');
        const vatRow = document.querySelector('.vat-row');

        vatCheckbox.addEventListener('change', function() {
            toggleVatDisplay(this.checked);
            recalculateAllRows();
        });

        function toggleVatDisplay(showVat) {
            // Toggle VAT column in table
            const vatColumns = document.querySelectorAll('.vat-column');
            vatColumns.forEach(col => {
                col.style.display = showVat ? '' : 'none';
            });

            // Toggle VAT row in totals
            if (vatRow) {
                vatRow.style.display = showVat ? '' : 'none';
            }
        }

        function isVatEnabled() {
            return vatCheckbox.checked;
        }

        /**
         * CUSTOMER AUTOCOMPLETE
         */
        const companyInput = document.getElementById('company_name');
        const customerDropdown = document.getElementById('customerDropdown');
        let customerTimeout = null;

        companyInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(customerTimeout);
            document.getElementById('customer_ref_id').value = '';
            document.getElementById('customer_id').value = '';
            
            if (query.length < 1) {
                customerDropdown.classList.remove('show');
                return;
            }
            customerTimeout = setTimeout(() => searchCustomers(query), 250);
        });

        companyInput.addEventListener('focus', function() {
            if (this.value.length >= 1) searchCustomers(this.value);
        });

        function searchCustomers(query) {
            customerDropdown.innerHTML = '<div class="loading-text"><i class="bi bi-hourglass-split"></i> Searching...</div>';
            customerDropdown.classList.add('show');
            
            fetch('api/search_customers.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    customerDropdown.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(c => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerHTML = `
                                <div>
                                    <span class="item-id">${escapeHtml(c.customer_id)}</span>
                                    <span class="main-text">${escapeHtml(c.company_name)}</span>
                                </div>
                                <div class="sub-text">
                                    <i class="bi bi-person"></i> ${escapeHtml(c.contact_person || '-')} |
                                    <i class="bi bi-telephone"></i> ${escapeHtml(c.phone || '-')}
                                </div>
                            `;
                            item.onclick = () => selectCustomer(c);
                            customerDropdown.appendChild(item);
                        });
                    }
                    
                    // Add new option
                    const newItem = document.createElement('div');
                    newItem.className = 'autocomplete-item';
                    newItem.style.background = '#f0fff0';
                    newItem.innerHTML = `
                        <span class="main-text">"${escapeHtml(query)}"</span>
                        <span class="new-badge">+ NEW</span>
                        <div class="sub-text">Add as new company</div>
                    `;
                    newItem.onclick = () => {
                        document.getElementById('customer_ref_id').value = '';
                        document.getElementById('customer_id').value = 'NEW';
                        customerDropdown.classList.remove('show');
                    };
                    customerDropdown.appendChild(newItem);
                })
                .catch(err => {
                    customerDropdown.innerHTML = '<div class="loading-text text-danger">Error loading</div>';
                });
        }

        function selectCustomer(c) {
            document.getElementById('customer_ref_id').value = c.id;
            document.getElementById('customer_id').value = c.customer_id;
            document.getElementById('company_name').value = c.company_name;
            document.getElementById('contact_person').value = c.contact_person || '';
            document.getElementById('phone').value = c.phone || '';
            document.getElementById('email').value = c.email || '';
            document.getElementById('address').value = c.address || '';
            customerDropdown.classList.remove('show');
        }

        document.addEventListener('click', function(e) {
            if (!companyInput.contains(e.target) && !customerDropdown.contains(e.target)) {
                customerDropdown.classList.remove('show');
            }
        });

        /**
         * ITEMS / PRODUCTS
         */
        let itemCounter = 0;

        document.addEventListener('DOMContentLoaded', () => {
            addItemRow();
            toggleVatDisplay(vatCheckbox.checked);
        });

        document.getElementById('addItemBtn').addEventListener('click', () => addItemRow());
        document.getElementById('discountInput').addEventListener('input', () => calculateTotals());

        function addItemRow() {
            itemCounter++;
            const tbody = document.getElementById('itemsTableBody');
            const row = document.createElement('tr');
            row.id = `item-row-${itemCounter}`;
            
            const vatDisplay = isVatEnabled() ? '' : 'display:none';
            
            row.innerHTML = `
                <td>
                    <input type="hidden" name="items[${itemCounter}][product_ref_id]" class="product-ref-id" value="">
                    <input type="text" class="form-control form-control-sm bg-light product-id" 
                           name="items[${itemCounter}][product_id]" readonly placeholder="Auto">
                </td>
                <td>
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm product-name" 
                               name="items[${itemCounter}][product_name]" required autocomplete="off"
                               data-row="${itemCounter}" placeholder="Type product name...">
                        <div class="product-autocomplete-dropdown" id="productDropdown${itemCounter}"></div>
                    </div>
                </td>
                <td>
                    <textarea class="form-control form-control-sm product-desc" 
                              name="items[${itemCounter}][product_description]" rows="2"></textarea>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm product-warranty" 
                           name="items[${itemCounter}][warranty]" placeholder="1 Year">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-qty" 
                           name="items[${itemCounter}][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-price" 
                           name="items[${itemCounter}][unit_price]" value="0" step="0.01" min="0" required>
                </td>
                <td class="vat-column" style="${vatDisplay}">
                    <input type="number" class="form-control form-control-sm bg-light item-vat" 
                           name="items[${itemCounter}][vat_amount]" value="0" readonly>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm bg-light fw-bold item-total" 
                           name="items[${itemCounter}][line_total]" value="0" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${itemCounter})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            setupProductAutocomplete(itemCounter);
            setupRowCalc(itemCounter);
        }

        function setupProductAutocomplete(rowId) {
            const row = document.getElementById(`item-row-${rowId}`);
            const input = row.querySelector('.product-name');
            const dropdown = document.getElementById(`productDropdown${rowId}`);
            let timeout = null;

            input.addEventListener('input', function() {
                const q = this.value.trim();
                clearTimeout(timeout);
                row.querySelector('.product-ref-id').value = '';
                row.querySelector('.product-id').value = '';
                
                if (q.length < 1) {
                    dropdown.classList.remove('show');
                    return;
                }
                timeout = setTimeout(() => searchProducts(q, rowId), 250);
            });

            input.addEventListener('focus', function() {
                if (this.value.length >= 1) searchProducts(this.value, rowId);
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }

        function searchProducts(query, rowId) {
            const dropdown = document.getElementById(`productDropdown${rowId}`);
            dropdown.innerHTML = '<div class="loading-text"><i class="bi bi-hourglass-split"></i> Searching...</div>';
            dropdown.classList.add('show');

            fetch('api/search_products.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(p => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerHTML = `
                                <span class="price-badge">Rs. ${formatNum(p.unit_price)}</span>
                                <div>
                                    <span class="item-id">${escapeHtml(p.product_id)}</span>
                                    <span class="main-text">${escapeHtml(p.product_name)}</span>
                                </div>
                                <div class="sub-text">
                                    ${p.warranty ? '<i class="bi bi-shield-check"></i> ' + escapeHtml(p.warranty) : 'No warranty'}
                                </div>
                            `;
                            item.onclick = () => selectProduct(p, rowId);
                            dropdown.appendChild(item);
                        });
                    }

                    // Add new option
                    const newItem = document.createElement('div');
                    newItem.className = 'autocomplete-item';
                    newItem.style.background = '#f0fff0';
                    newItem.innerHTML = `
                        <span class="main-text">"${escapeHtml(query)}"</span>
                        <span class="new-badge">+ NEW</span>
                        <div class="sub-text">Add as new product</div>
                    `;
                    newItem.onclick = () => {
                        const row = document.getElementById(`item-row-${rowId}`);
                        row.querySelector('.product-ref-id').value = '';
                        row.querySelector('.product-id').value = 'NEW';
                        dropdown.classList.remove('show');
                    };
                    dropdown.appendChild(newItem);
                })
                .catch(err => {
                    dropdown.innerHTML = '<div class="loading-text text-danger">Error loading</div>';
                });
        }

        function selectProduct(p, rowId) {
            const row = document.getElementById(`item-row-${rowId}`);
            row.querySelector('.product-ref-id').value = p.id;
            row.querySelector('.product-id').value = p.product_id;
            row.querySelector('.product-name').value = p.product_name;
            row.querySelector('.product-desc').value = p.product_description || '';
            row.querySelector('.product-warranty').value = p.warranty || '';
            row.querySelector('.item-price').value = p.unit_price;
            document.getElementById(`productDropdown${rowId}`).classList.remove('show');
            calculateRow(rowId);
        }

        function setupRowCalc(rowId) {
            const row = document.getElementById(`item-row-${rowId}`);
            row.querySelector('.item-qty').addEventListener('input', () => calculateRow(rowId));
            row.querySelector('.item-price').addEventListener('input', () => calculateRow(rowId));
        }

        function removeRow(rowId) {
            document.getElementById(`item-row-${rowId}`)?.remove();
            calculateTotals();
        }

        function calculateRow(rowId) {
            const row = document.getElementById(`item-row-${rowId}`);
            if (!row) return;
            
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const lineTotal = qty * price;
            
            let vat = 0;
            let total = lineTotal;
            
            if (isVatEnabled()) {
                vat = lineTotal * 0.18;
                total = lineTotal + vat;
            }
            
            row.querySelector('.item-vat').value = vat.toFixed(2);
            row.querySelector('.item-total').value = total.toFixed(2);
            
            calculateTotals();
        }

        function recalculateAllRows() {
            const rows = document.querySelectorAll('#itemsTableBody tr');
            rows.forEach(row => {
                const rowId = row.id.replace('item-row-', '');
                calculateRow(rowId);
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            
            document.querySelectorAll('.item-total').forEach(inp => {
                const total = parseFloat(inp.value) || 0;
                const row = inp.closest('tr');
                const vat = parseFloat(row.querySelector('.item-vat').value) || 0;
                subtotal += (total - vat);
            });
            
            const discount = parseFloat(document.getElementById('discountInput').value) || 0;
            const afterDiscount = subtotal - discount;
            
            let tax = 0;
            if (isVatEnabled()) {
                tax = afterDiscount * 0.18;
            }
            
            const grand = afterDiscount + tax;

            document.getElementById('subtotalDisplay').textContent = 'Rs. ' + formatNum(subtotal);
            document.getElementById('taxDisplay').textContent = 'Rs. ' + formatNum(tax);
            document.getElementById('grandTotalDisplay').textContent = 'Rs. ' + formatNum(grand);
            document.getElementById('subtotalInput').value = subtotal.toFixed(2);
            document.getElementById('taxInput').value = tax.toFixed(2);
            document.getElementById('grandTotalInput').value = grand.toFixed(2);
        }

        function formatNum(n) {
            return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function escapeHtml(t) {
            if (!t) return '';
            const d = document.createElement('div');
            d.textContent = t;
            return d.innerHTML;
        }

        // Form validation
        document.getElementById('quotationForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('#itemsTableBody tr');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item.');
                return;
            }
            let valid = true;
            rows.forEach(r => {
                if (!r.querySelector('.product-name').value.trim()) valid = false;
                if (parseFloat(r.querySelector('.item-qty').value) <= 0) valid = false;
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill product name and quantity for all items.');
            }
        });
    </script>
</body>
</html>