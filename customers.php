<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage("Customer deleted successfully.", 'success');
    redirect('customers.php');
}

// Fetch all customers
$stmt = $pdo->prepare("SELECT * FROM customers ORDER BY company_name ASC");
$stmt->execute();
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - DOT INK</title>
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
                            <a class="nav-link text-white" href="quotation_list.php">
                                <i class="bi bi-file-earmark-text"></i> Quotations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="create_quotation.php">
                                <i class="bi bi-plus-circle"></i> New Quotation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Customers</h1>
                </div>

                <?php displayFlashMessage(); ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Customer ID</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($customers) > 0): ?>
                                <?php foreach ($customers as $index => $customer): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($customer['customer_id'] ?? '-') ?></strong></td>
                                        <td><strong><?= htmlspecialchars($customer['company_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($customer['contact_person'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($customer['address'] ?? '-') ?></td>
                                        <td>
                                            <a href="customers.php?delete=<?= $customer['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p>No customers found. Customers will be added automatically when you create quotations.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

</body>
</html>