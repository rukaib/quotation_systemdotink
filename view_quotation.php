<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) redirect('quotation_list.php');

// Fetch quotation
$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute([$id]);
$q = $stmt->fetch();

if (!$q) {
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
    <title>View Quotation - <?= htmlspecialchars($q['quotation_no']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; min-height: 100vh; }
        .sidebar .nav-link { color: rgba(255,255,255,.8); padding: .75rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { width: 120px; font-weight: 600; color: #666; }
        .info-value { flex: 1; color: #000; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3 mb-3"><i class="bi bi-printer"></i> DOT INK</h5>
                    <ul class="nav flex-column">
                        <li><a class="nav-link active text-white" href="quotation_list.php"><i class="bi bi-file-earmark-text"></i> Quotations</a></li>
                        <li><a class="nav-link text-white" href="create_quotation.php"><i class="bi bi-plus-circle"></i> New Quotation</a></li>
                        <li><a class="nav-link text-white" href="customers.php"><i class="bi bi-people"></i> Customers</a></li>
                        <li><a class="nav-link text-white" href="products.php"><i class="bi bi-box"></i> Products</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-text"></i> <?= htmlspecialchars($q['quotation_no']) ?></h1>
                    <div>
                        <a href="quotation_list.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                        <a href="print_quotation.php?id=<?= $id ?>" class="btn btn-primary" target="_blank"><i class="bi bi-printer"></i> Print</a>
                        <a href="edit_quotation.php?id=<?= $id ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                    </div>
                </div>

                <?php displayFlashMessage(); ?>

                <div class="row">
                    <!-- Quotation Info -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white"><h6 class="mb-0">Quotation Details</h6></div>
                            <div class="card-body">
                                <div class="info-row"><span class="info-label">Quotation No:</span><span class="info-value"><strong><?= htmlspecialchars($q['quotation_no']) ?></strong></span></div>
                                <div class="info-row"><span class="info-label">Date:</span><span class="info-value"><?= date('F d, Y', strtotime($q['quotation_date'])) ?></span></div>
                                <div class="info-row"><span class="info-label">Prepared By:</span><span class="info-value"><?= htmlspecialchars($q['prepared_by']) ?></span></div>
                                <div class="info-row"><span class="info-label">Status:</span><span class="info-value"><span class="badge bg-info"><?= ucfirst($q['status'] ?? 'draft') ?></span></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white"><h6 class="mb-0">Company Information</h6></div>
                            <div class="card-body">
                                <div class="info-row"><span class="info-label">Customer ID:</span><span class="info-value"><strong><?= htmlspecialchars($q['customer_id'] ?? '-') ?></strong></span></div>
                                <div class="info-row"><span class="info-label">Company:</span><span class="info-value"><strong><?= htmlspecialchars($q['company_name']) ?></strong></span></div>
                                <div class="info-row"><span class="info-label">Contact:</span><span class="info-value"><?= htmlspecialchars($q['contact_person'] ?? '-') ?></span></div>
                                <div class="info-row"><span class="info-label">Phone:</span><span class="info-value"><?= htmlspecialchars($q['phone'] ?? '-') ?></span></div>
                                <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?= htmlspecialchars($q['email'] ?? '-') ?></span></div>
                                <div class="info-row"><span class="info-label">Address:</span><span class="info-value"><?= htmlspecialchars($q['address'] ?? '-') ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white"><h6 class="mb-0">Terms & Conditions</h6></div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3"><strong>Delivery:</strong><br><?= htmlspecialchars($q['delivery_terms']) ?></div>
                            <div class="col-md-3"><strong>Validity:</strong><br><?= htmlspecialchars($q['validity']) ?></div>
                            <div class="col-md-3"><strong>Payment:</strong><br><?= htmlspecialchars($q['payment_terms']) ?></div>
                            <div class="col-md-3"><strong>Stock:</strong><br><?= htmlspecialchars($q['stock_availability']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white"><h6 class="mb-0">Quotation Items</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Warranty</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>VAT 18%</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($items) > 0): ?>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['product_id'] ?? '-') ?></strong></td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                            <?php if (!empty($item['product_description'])): ?>
                                                <br><small class="text-muted"><?= nl2br(htmlspecialchars($item['product_description'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['warranty'] ?? '-') ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>Rs. <?= number_format($item['unit_price'], 2) ?></td>
                                        <td>Rs. <?= number_format($item['vat_amount'], 2) ?></td>
                                        <td><strong>Rs. <?= number_format($item['line_total'], 2) ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center py-3">No items found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals & Notes -->
                <div class="row">
                    <div class="col-md-7">
                        <?php if (!empty($q['notes'])): ?>
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Notes</h6></div>
                            <div class="card-body"><?= nl2br(htmlspecialchars($q['notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-dark text-white"><h6 class="mb-0">Summary</h6></div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>Rs. <?= number_format($q['subtotal'], 2) ?></strong>
                                </div>
                                <?php if ($q['discount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span>Discount:</span>
                                    <strong>- Rs. <?= number_format($q['discount'], 2) ?></strong>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>VAT (18%):</span>
                                    <strong>Rs. <?= number_format($q['tax_amount'], 2) ?></strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <h5>Grand Total:</h5>
                                    <h5 class="text-primary">Rs. <?= number_format($q['grand_total'], 2) ?></h5>
                                </div>
                                <p class="text-muted small mt-2"><em><?= numberToWords($q['grand_total']) ?></em></p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>