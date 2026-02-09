
<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage("Product deleted successfully.", 'success');
    redirect('products.php');
}

// Fetch all products - FIXED: use product_name instead of item_name
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY product_name ASC");
$stmt->execute();
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - DOT INK</title>
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
                            <a class="nav-link text-white" href="customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Products</h1>
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </a>
                </div>

                <?php displayFlashMessage(); ?>

                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchProduct" class="form-control" 
                               placeholder="Search by Product ID or Name...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="productsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Warranty</th>
                                <th>Unit Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $index => $product): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($product['product_id']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($product['product_name']) ?></strong>
                                        </td>
                                        <td>
                                            <small><?= nl2br(htmlspecialchars(substr($product['product_description'] ?? '', 0, 100))) ?></small>
                                            <?php if (strlen($product['product_description'] ?? '') > 100): ?>
                                                <small class="text-muted">...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['warranty'] ?? '-') ?></td>
                                        <td>
                                            <strong>Rs. <?= number_format($product['unit_price'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_product.php?id=<?= $product['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="products.php?delete=<?= $product['id'] ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this product?')"
                                                   title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-box display-4 text-muted"></i>
                                        <p class="mt-2">No products found.</p>
                                        <a href="add_product.php" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-circle"></i> Add First Product
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Product Count -->
                <div class="text-muted mb-3">
                    Total Products: <strong><?= count($products) ?></strong>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Live Search Script -->
    <script>
    document.getElementById('searchProduct').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#productsTable tbody tr');
        
        rows.forEach(function(row) {
            const productId = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
            const productName = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
            
            if (productId.includes(filter) || productName.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
