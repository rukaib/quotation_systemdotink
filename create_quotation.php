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
      top: 0; bottom: 0; left: 0;
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
      top: 100%; left: 0; right: 0;
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

    .card { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

    /* VAT Toggle Styling */
    .vat-toggle-box {
      background: #fff3cd;
      border: 2px solid #ffc107;
      border-radius: 8px;
      padding: 10px 15px;
      margin-bottom: 15px;
    }
    .vat-toggle-box .form-check-input { width: 20px; height: 20px; margin-right: 10px; }
    .vat-toggle-box label { font-weight: 600; font-size: 14px; }

    /* Items display table */
    #itemsDisplayTable { font-size: 13px; }
    #itemsDisplayTable .btn-sm { padding: 2px 6px; font-size: 11px; }

    /* Popup Modal styles */
    .product-search-modal .modal-dialog { max-width: 980px; }
    .product-search-modal .modal-body { max-height: 72vh; overflow-y: auto; }

    .search-box-container {
      position: sticky;
      top: 0;
      background: #fff;
      padding: 10px 0;
      z-index: 10;
      border-bottom: 1px solid #ddd;
    }
    .selected-items-count {
      background: #0d6efd;
      color: #fff;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: 600;
      display: inline-block;
    }

    .product-card {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 10px;
      transition: all 0.2s;
      background: #fff;
    }
    .product-card.selected {
      border-color: #0d6efd;
      background-color: #e7f1ff;
    }
    .product-id-badge {
      background: #0070C0;
      color: #fff;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
    }
    .product-price {
      background: #28a745;
      color: #fff;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: 600;
      white-space: nowrap;
    }

    .vat-column { }
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
                    <input type="text" class="form-control bg-light" name="quotation_no" value="<?= $quotationNo ?>" readonly>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="quotation_date" value="<?= $currentDate ?>" required>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Prepared By</label>
                  <input type="text" class="form-control" name="prepared_by" value="M.ASHAN" required>
                </div>

                <!-- VAT TOGGLE -->
                <div class="vat-toggle-box">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="vatEnabled" name="vat_enabled" value="1" checked>
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
                    <input type="text" class="form-control bg-light" name="customer_id" id="customer_id" placeholder="Auto" readonly>
                  </div>

                  <div class="col-md-9 mb-3">
                    <label class="form-label">Company Name * <small class="text-muted">(Type to search)</small></label>
                    <div class="autocomplete-wrapper">
                      <input type="text" class="form-control" name="company_name" id="company_name" required autocomplete="off"
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
                <input type="text" class="form-control" name="delivery_terms" value="Within 2 Days after Confirmation">
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

        <!-- Items (display) -->
        <div class="card mb-3">
          <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-cart3"></i> Quotation Items</h6>
            <button type="button" class="btn btn-light btn-sm" id="openProductPopup">
              <i class="bi bi-plus-circle"></i> Add Items
            </button>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-sm mb-0" id="itemsDisplayTable">
                <thead class="table-light">
                <tr>
                  <th style="width:50px;">#</th>
                  <th style="width:90px;">Product ID</th>
                  <th>Product Name</th>
                  <th>Description</th>
                  <th style="width:130px;">Warranty</th>
                  <th style="width:70px;">Qty</th>
                  <th style="width:120px;">Price</th>
                  <th style="width:120px;" class="vat-column">VAT 18%</th>
                  <th style="width:130px;">Total</th>
                  <th style="width:70px;"></th>
                </tr>
                </thead>
                <tbody id="itemsDisplayBody">
                <tr id="noItemsRow">
                  <td colspan="10" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No items added yet. Click "Add Items" to add products.
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Hidden items for submission -->
        <div id="hiddenItemsContainer"></div>

        <!-- Totals -->
        <div class="row">
          <div class="col-md-7">
            <div class="card">
              <div class="card-header"><h6 class="mb-0"><i class="bi bi-sticky"></i> Notes</h6></div>
              <div class="card-body">
                <textarea class="form-control" name="notes" rows="4">Price is negotiable after discussions.
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
                    <input type="number" class="form-control" name="discount" id="discountInput" value="0" step="0.01" min="0">
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

<!-- Product Selection Popup Modal -->
<div class="modal fade product-search-modal" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="productModalLabel"><i class="bi bi-box-seam"></i> Select Products</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="search-box-container">
          <div class="row g-2 align-items-center">
            <div class="col-md-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="productSearchInput"
                       placeholder="Search by product name, ID, or description..." autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn"><i class="bi bi-x-lg"></i></button>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <span class="selected-items-count">
                <i class="bi bi-cart3"></i> <span id="selectedCount">0</span> selected
              </span>
            </div>
          </div>
        </div>

        <div class="mt-3" id="productListContainer">
          <div class="text-center text-muted py-4" id="productLoadingText">
            <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
            Loading products...
          </div>
          <div id="productList"></div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x"></i> Cancel
        </button>
        <button type="button" class="btn btn-success" id="confirmProductSelection">
          <i class="bi bi-check-lg"></i> Add Selected Items
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  /**
   * DATA
   */
  let quotationItems = [];        // Final items in quotation
  let tempSelectedProducts = [];  // Selected in popup
  let allProducts = [];

  /**
   * VAT
   */
  const vatCheckbox = document.getElementById('vatEnabled');
  const vatRow = document.querySelector('.vat-row');

  vatCheckbox.addEventListener('change', function() {
    toggleVatDisplay(this.checked);
    renderItemsTable();
    calculateTotals();
  });

  function isVatEnabled() { return vatCheckbox.checked; }

  function toggleVatDisplay(showVat) {
    document.querySelectorAll('.vat-column').forEach(col => {
      col.style.display = showVat ? '' : 'none';
    });
    if (vatRow) vatRow.style.display = showVat ? '' : 'none';
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
      .catch(() => {
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
   * WARRANTY HELPERS
   */
  function normalizeWarrantyValue(w) {
    if (!w) return '';
    const s = String(w).trim();
    if (s.toLowerCase() === 'no warranty') return '';
    return s;
  }

  function optionHtml(value, label, selected) {
    return `<option value="${escapeHtml(value)}" ${selected ? 'selected' : ''}>${escapeHtml(label)}</option>`;
  }

  function buildWarrantyOptions(currentWarranty) {
    const current = normalizeWarrantyValue(currentWarranty);
    const opts = [];
    opts.push(optionHtml('', 'No Warranty', current === ''));

    for (let y = 1; y <= 10; y++) {
      const label = `${y} Year${y > 1 ? 's' : ''} Warranty`;
      opts.push(optionHtml(label, label, current === label));
    }
    return opts.join('');
  }

  /**
   * PRODUCT POPUP
   */
  const productModal = new bootstrap.Modal(document.getElementById('productModal'));

  document.getElementById('openProductPopup').addEventListener('click', function() {
    tempSelectedProducts = [];
    updateSelectedCount();
    loadAllProducts('');
    productModal.show();
  });

  let searchTimeout = null;
  document.getElementById('productSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    searchTimeout = setTimeout(() => loadAllProducts(query), 250);
  });

  document.getElementById('clearSearchBtn').addEventListener('click', function() {
    document.getElementById('productSearchInput').value = '';
    loadAllProducts('');
  });

  function loadAllProducts(searchQuery = '') {
    const container = document.getElementById('productList');
    const loadingText = document.getElementById('productLoadingText');

    loadingText.style.display = 'block';
    container.innerHTML = '';

    const url = 'api/search_products.php?q=' + encodeURIComponent(searchQuery || '');

    fetch(url)
      .then(res => res.json())
      .then(data => {
        loadingText.style.display = 'none';
        allProducts = data || [];

        if (!allProducts.length) {
          container.innerHTML = `<div class="text-center text-muted py-4">No products found.</div>`;
          return;
        }

        allProducts.forEach(p => {
          const isAlreadyAdded = quotationItems.some(item => String(item.product_ref_id) === String(p.id));
          const isSelected = tempSelectedProducts.some(item => String(item.product_ref_id) === String(p.id));
          const selectedItem = tempSelectedProducts.find(i => String(i.product_ref_id) === String(p.id));

          const card = document.createElement('div');
          card.className = 'product-card' + (isSelected ? ' selected' : '');
          card.dataset.productId = p.id;

          card.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <span class="product-id-badge">${escapeHtml(p.product_id)}</span>
                <strong class="ms-2">${escapeHtml(p.product_name)}</strong>
                ${isAlreadyAdded ? '<span class="badge bg-info ms-2">Already Added</span>' : ''}
              </div>
              <span class="product-price">Rs. ${formatNum(p.unit_price)}</span>
            </div>

            <div class="row mt-2 g-2">
              <div class="col-12">
                <label class="form-label small mb-1">Description</label>
                <input type="text" class="form-control form-control-sm popup-desc"
                  value="${escapeHtml(selectedItem?.product_description ?? p.product_description ?? '')}"
                  data-product-id="${p.id}">
              </div>

              <div class="col-md-4">
                <label class="form-label small mb-1">Warranty</label>
                <select class="form-select form-select-sm popup-warranty" data-product-id="${p.id}">
                  ${buildWarrantyOptions(selectedItem?.warranty ?? p.warranty ?? '')}
                </select>
                <div class="form-text small text-muted">“No Warranty” will be saved as blank.</div>
              </div>

              <div class="col-md-3">
                <label class="form-label small mb-1">Qty</label>
                <input type="number" class="form-control form-control-sm qty-input-popup"
                  value="${selectedItem?.quantity ?? 1}" min="1" data-product-id="${p.id}">
              </div>

              <div class="col-md-3">
                <label class="form-label small mb-1">Unit Price</label>
                <input type="number" class="form-control form-control-sm popup-price"
                  value="${selectedItem?.unit_price ?? p.unit_price ?? 0}"
                  step="0.01" min="0" data-product-id="${p.id}">
              </div>

              <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm ${isSelected ? 'btn-danger' : 'btn-outline-success'} w-100 toggle-select-btn">
                  <i class="bi ${isSelected ? 'bi-dash' : 'bi-plus'}"></i> ${isSelected ? 'Remove' : 'Select'}
                </button>
              </div>
            </div>
          `;

          // Toggle selection
          card.querySelector('.toggle-select-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            toggleProductSelection(p, card);
          });

          // Live updates (only if selected)
          card.querySelector('.qty-input-popup').addEventListener('input', function(e) {
            e.stopPropagation();
            updateTempField(this.dataset.productId, 'quantity', parseInt(this.value) || 1);
          });

          card.querySelector('.popup-price').addEventListener('input', function(e) {
            e.stopPropagation();
            updateTempField(this.dataset.productId, 'unit_price', parseFloat(this.value) || 0);
          });

          card.querySelector('.popup-desc').addEventListener('input', function(e) {
            e.stopPropagation();
            updateTempField(this.dataset.productId, 'product_description', this.value || '');
          });

          card.querySelector('.popup-warranty').addEventListener('change', function(e) {
            e.stopPropagation();
            updateTempField(this.dataset.productId, 'warranty', normalizeWarrantyValue(this.value));
          });

          container.appendChild(card);
        });
      })
      .catch(() => {
        loadingText.style.display = 'none';
        container.innerHTML = `<div class="text-center text-danger py-4">Error loading products.</div>`;
      });
  }

  function updateTempField(productId, field, value) {
    const item = tempSelectedProducts.find(p => String(p.product_ref_id) === String(productId));
    if (item) item[field] = value;
  }

  function toggleProductSelection(product, cardElement) {
    const index = tempSelectedProducts.findIndex(p => String(p.product_ref_id) === String(product.id));

    const qty = parseInt(cardElement.querySelector('.qty-input-popup').value) || 1;
    const price = parseFloat(cardElement.querySelector('.popup-price').value) || 0;
    const desc = cardElement.querySelector('.popup-desc').value || '';
    const warrantySel = cardElement.querySelector('.popup-warranty').value || '';
    const warranty = normalizeWarrantyValue(warrantySel);

    const btn = cardElement.querySelector('.toggle-select-btn');

    if (index > -1) {
      tempSelectedProducts.splice(index, 1);
      cardElement.classList.remove('selected');
      btn.className = 'btn btn-sm btn-outline-success w-100 toggle-select-btn';
      btn.innerHTML = '<i class="bi bi-plus"></i> Select';
    } else {
      tempSelectedProducts.push({
        product_ref_id: product.id,
        product_id: product.product_id,
        product_name: product.product_name,
        product_description: desc,
        warranty: warranty,
        quantity: qty,
        unit_price: price
      });

      cardElement.classList.add('selected');
      btn.className = 'btn btn-sm btn-danger w-100 toggle-select-btn';
      btn.innerHTML = '<i class="bi bi-dash"></i> Remove';
    }

    updateSelectedCount();
  }

  function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = tempSelectedProducts.length;
  }

  document.getElementById('confirmProductSelection').addEventListener('click', function() {
    if (tempSelectedProducts.length === 0) {
      alert('Please select at least one product');
      return;
    }

    tempSelectedProducts.forEach(product => {
      const existingIndex = quotationItems.findIndex(item =>
        item.product_ref_id && String(item.product_ref_id) === String(product.product_ref_id)
      );

      if (existingIndex > -1) {
        quotationItems[existingIndex].quantity += product.quantity;
        // also update editable fields (optional, but usually expected)
        quotationItems[existingIndex].unit_price = product.unit_price;
        quotationItems[existingIndex].product_description = product.product_description;
        quotationItems[existingIndex].warranty = product.warranty;
      } else {
        quotationItems.push({...product});
      }
    });

    tempSelectedProducts = [];
    productModal.hide();
    renderItemsTable();
    calculateTotals();
  });

  /**
   * RENDER QUOTATION ITEMS TABLE + HIDDEN INPUTS
   */
  function renderItemsTable() {
    const tbody = document.getElementById('itemsDisplayBody');
    const hiddenContainer = document.getElementById('hiddenItemsContainer');

    tbody.innerHTML = '';
    hiddenContainer.innerHTML = '';

    if (quotationItems.length === 0) {
      tbody.innerHTML = `
        <tr id="noItemsRow">
          <td colspan="10" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No items added yet. Click "Add Items" to add products.
          </td>
        </tr>
      `;
      return;
    }

    const vatEnabled = isVatEnabled();

    quotationItems.forEach((item, index) => {
      const lineTotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
      const vat = vatEnabled ? lineTotal * 0.18 : 0;
      const total = lineTotal + vat;

      item.vat_amount = vat;
      item.line_total = total;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${index + 1}</td>
        <td><span class="badge bg-primary">${escapeHtml(item.product_id || '')}</span></td>
        <td><strong>${escapeHtml(item.product_name || '')}</strong></td>
        <td><small class="text-muted">${escapeHtml(item.product_description || '')}</small></td>
        <td>${escapeHtml(item.warranty || '')}</td>
        <td class="text-center"><strong>${escapeHtml(item.quantity)}</strong></td>
        <td class="text-end">Rs. ${formatNum(item.unit_price)}</td>
        <td class="text-end vat-column" style="${vatEnabled ? '' : 'display:none'}">Rs. ${formatNum(vat)}</td>
        <td class="text-end"><strong>Rs. ${formatNum(total)}</strong></td>
        <td class="text-center">
          <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);

      // Hidden inputs for save_quotation.php
      hiddenContainer.innerHTML += `
        <input type="hidden" name="items[${index}][product_ref_id]" value="${escapeAttr(item.product_ref_id)}">
        <input type="hidden" name="items[${index}][product_id]" value="${escapeAttr(item.product_id)}">
        <input type="hidden" name="items[${index}][product_name]" value="${escapeAttr(item.product_name)}">
        <input type="hidden" name="items[${index}][product_description]" value="${escapeAttr(item.product_description)}">
        <input type="hidden" name="items[${index}][warranty]" value="${escapeAttr(item.warranty)}">
        <input type="hidden" name="items[${index}][quantity]" value="${escapeAttr(item.quantity)}">
        <input type="hidden" name="items[${index}][unit_price]" value="${escapeAttr(item.unit_price)}">
        <input type="hidden" name="items[${index}][vat_amount]" value="${escapeAttr(vat.toFixed(2))}">
        <input type="hidden" name="items[${index}][line_total]" value="${escapeAttr(total.toFixed(2))}">
      `;
    });
  }

  function removeItem(index) {
    if (!confirm('Remove this item?')) return;
    quotationItems.splice(index, 1);
    renderItemsTable();
    calculateTotals();
  }

  /**
   * TOTALS
   */
  function calculateTotals() {
    let subtotal = 0;
    quotationItems.forEach(item => {
      const qty = parseFloat(item.quantity) || 0;
      const price = parseFloat(item.unit_price) || 0;
      subtotal += qty * price;
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const afterDiscount = subtotal - discount;

    let tax = 0;
    if (isVatEnabled()) tax = afterDiscount * 0.18;

    const grand = afterDiscount + tax;

    document.getElementById('subtotalDisplay').textContent = 'Rs. ' + formatNum(subtotal);
    document.getElementById('taxDisplay').textContent = 'Rs. ' + formatNum(tax);
    document.getElementById('grandTotalDisplay').textContent = 'Rs. ' + formatNum(grand);

    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('taxInput').value = tax.toFixed(2);
    document.getElementById('grandTotalInput').value = grand.toFixed(2);
  }

  document.getElementById('discountInput').addEventListener('input', calculateTotals);

  /**
   * UTIL
   */
  function formatNum(n) {
    return (parseFloat(n) || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function escapeHtml(t) {
    if (t === null || t === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(t);
    return d.innerHTML;
  }

  // For hidden input attributes
  function escapeAttr(t) {
    if (t === null || t === undefined) return '';
    return String(t)
      .replaceAll('&', '&amp;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');
  }

  /**
   * VALIDATION
   */
  document.getElementById('quotationForm').addEventListener('submit', function(e) {
    if (quotationItems.length === 0) {
      e.preventDefault();
      alert('Please add at least one item.');
    }
  });

  /**
   * INIT
   */
  document.addEventListener('DOMContentLoaded', function() {
    toggleVatDisplay(vatCheckbox.checked);
    renderItemsTable();
    calculateTotals();
  });
</script>

</body>
</html>