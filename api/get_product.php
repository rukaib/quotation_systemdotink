<?php
/**
 * API: Search Products by Product ID or Product Name
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($search) < 1) {
    echo json_encode([]);
    exit;
}

try {
    // Search by product_id OR product_name
    $stmt = $pdo->prepare("
        SELECT 
            id,
            product_id,
            product_name,
            product_description,
            warranty,
            unit_price
        FROM products 
        WHERE product_id LIKE :search 
           OR product_name LIKE :search 
           OR product_description LIKE :search
        ORDER BY product_name ASC
        LIMIT 15
    ");
    
    $searchTerm = "%{$search}%";
    $stmt->execute(['search' => $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>