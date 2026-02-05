<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    redirect('quotation_list.php');
}

// Fetch quotation
$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute([$id]);
$quotation = $stmt->fetch();

if (!$quotation) {
    setFlashMessage("Quotation not found.", 'error');
    redirect('quotation_list.php');
}

// Fetch items
$stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY item_order");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
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
            <nav class="col-md-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3">DOT INK</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="quotation_list.php">
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
                    <a href="view_quotation.php?id=<?= $id ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <form id="quotationForm" method="POST" action="update_quotation.php">
                    <input type="hidden" name="quotation_id" value="<?= $id ?>">
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Quotation Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Quotation Number</label>
                                        <input type="text" class="form-control" name="quotation_no" 
                                               value="<?= htmlspecialchars($quotation['quotation_no']) ?>" readonly required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="quotation_date" 
                                               value="<?= $quotation['quotation_date'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Prepared By</label>
                                        <input type="text" class="form-control" name="prepared_by" 
                                               value="<?= htmlspecialchars($quotation['prepared_by']) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Customer Name *</label>
                                        <input type="text" class="form-control" name="customer_name" 
                                               value="<?= htmlspecialchars($quotation['customer_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Organization</label>
                                        <input type="text" class="form-control" name="organization" 
                                               value="<?= htmlspecialchars($quotation['organization']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="customer_phone" 
                                               value="<?= htmlspecialchars($quotation['customer_phone']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="customer_email" 
                                               value="<?= htmlspecialchars($quotation['customer_email']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="customer_address" rows="2"><?= htmlspecialchars($quotation['customer_address']) ?></textarea>
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
                                    <label class="form-label">Delivery</label>
                                    <input type="text" class="form-control" name="delivery_terms" 
                                           value="<?= htmlspecialchars($quotation['delivery_terms']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Validity</label>
                                    <input type="text" class="form-control" name="validity" 
                                           value="<?= htmlspecialchars($quotation['validity']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payments</label>
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
                                            <th style="width: 30%;">Item Name *</th>
                                            <th style="width: 25%;">Description</th>
                                            <th style="width: 10%;">Warranty</th>
                                            <th style="width: 8%;">Qty *</th>
                                            <th style="width: 10%;">Price *</th>
                                            <th style="width: 10%;">VAT (18%)</th>
                                            <th style="width: 12%;">Total</th>
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
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="items[<?= $counter ?>][name]" 
                                                       value="<?= htmlspecialchars($item['item_name']) ?>" required>
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm" 
                                                          name="items[<?= $counter ?>][description]" 
                                                          rows="2"><?= htmlspecialchars($item['item_description']) ?></textarea>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="items[<?= $counter ?>][warranty]" 
                                                       value="<?= htmlspecialchars($item['warranty']) ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm item-qty" 
                                                       name="items[<?= $counter ?>][quantity]" 
                                                       value="<?= $item['quantity'] ?>" min="1" required 
                                                       data-row="<?= $counter ?>" onchange="calculateRow(<?= $counter ?>)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm item-price" 
                                                       name="items[<?= $counter ?>][price]" 
                                                       value="<?= $item['unit_price'] ?>" step="0.01" min="0" required 
                                                       data-row="<?= $counter ?>" onchange="calculateRow(<?= $counter ?>)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm item-vat" 
                                                       name="items[<?= $counter ?>][vat]" 
                                                       value="<?= $item['vat_amount'] ?>" step="0.01" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm item-total" 
                                                       name="items[<?= $counter ?>][total]" 
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
                                        <strong id="subtotalDisplay">Rs. 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <div class="input-group input-group-sm" style="width: 150px;">
                                            <input type="number" class="form-control" name="discount" 
                                                   id="discountInput" value="<?= $quotation['discount'] ?>" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (18%):</span>
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

                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                <i class="bi bi-save"></i> Update Quotation
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCounter = <?= $counter ?>;
    </script>
    <script src="assets/js/quotation.js"></script>
    <script>
        // Calculate totals on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();
        });
    </script>
</body>
</html>