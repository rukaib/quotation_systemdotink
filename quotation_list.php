<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Initialize variables for search inputs
$search_text = $_GET['search'] ?? '';
$search_date = $_GET['date'] ?? '';

// Base Query
$sql = "SELECT * FROM quotations WHERE 1=1";
$params = [];

// 1. Filter by Search Text (Quotation No, Customer ID, or Company Name)
if (!empty($search_text)) {
    $sql .= " AND (quotation_no LIKE :search_text 
              OR company_name LIKE :search_text 
              OR customer_id LIKE :search_text)";
    $params[':search_text'] = "%$search_text%";
}

// 2. Filter by Specific Date
if (!empty($search_date)) {
    $sql .= " AND quotation_date = :search_date";
    $params[':search_date'] = $search_date;
}

// 3. Sort by Date Descending (Newest first)
$sql .= " ORDER BY quotation_date DESC, id DESC";

// Execute Query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Quotations</h1>
                <a href="create_quotation.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Quotation</a>
            </div>

            <?php if (function_exists('displayFlashMessage')) displayFlashMessage(); ?>

            <!-- SEARCH FORM -->
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-5">
                            <label for="search" class="form-label fw-bold">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Quotation No, Customer ID or Company Name..." 
                                   value="<?= htmlspecialchars($search_text) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date" class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= htmlspecialchars($search_date) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                            <a href="quotation_list.php" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quotations Table -->
            <div class="card shadow-sm">
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
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($q['customer_id'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($q['company_name']) ?></td>
                                        <td><strong>Rs. <?= number_format($q['grand_total'], 2) ?></strong></td>
                                        <td>
                                            <?php 
                                                $statusClass = match($q['status']) {
                                                    'accepted' => 'bg-success',
                                                    'sent' => 'bg-primary',
                                                    'rejected' => 'bg-danger',
                                                    'expired' => 'bg-warning text-dark',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= ucfirst($q['status'] ?? 'draft') ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-info text-white" title="View"><i class="bi bi-eye"></i></a>
                                                <a href="print_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-secondary" title="Print" target="_blank"><i class="bi bi-printer"></i></a>
                                                <a href="edit_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-warning text-dark" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <a href="delete_quotation.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quotation?')" title="Delete"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No quotations found matching your criteria. 
                                        <?php if(!empty($search_text) || !empty($search_date)): ?>
                                            <br><a href="quotation_list.php">Clear filters</a>
                                        <?php else: ?>
                                            <br><a href="create_quotation.php">Create New Quotation</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
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