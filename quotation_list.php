<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$stmt = $pdo->prepare("SELECT * FROM quotations ORDER BY id DESC");
$stmt->execute();
$quotations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotations - DOT INK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; min-height: 100vh; }
        .sidebar .nav-link { color: rgba(255,255,255,.8); padding: .75rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
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
                    <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Quotations</h1>
                    <a href="create_quotation.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Quotation</a>
                </div>

                <?php displayFlashMessage(); ?>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Quotation No</th>
                                    <th>Date</th>
                                    <th>Customer ID</th>
                                    <th>Company</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($quotations) > 0): ?>
                                    <?php foreach ($quotations as $q): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($q['quotation_no']) ?></strong></td>
                                        <td><?= date('d M Y', strtotime($q['quotation_date'])) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($q['customer_id'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($q['company_name']) ?></td>
                                        <td><strong>Rs. <?= number_format($q['grand_total'], 2) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($q['status'] ?? 'draft') ?></span></td>
                                        <td>
                                            <a href="view_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="print_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-secondary" title="Print" target="_blank"><i class="bi bi-printer"></i></a>
                                            <a href="edit_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <a href="delete_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quotation?')" title="Delete"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center py-4">No quotations found. <a href="create_quotation.php">Create one</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>