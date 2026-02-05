<?php
/**
 * API: Search Products by ID or Product Name
 * Minimum 1 character to start search
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Start search from 1 character
if (strlen($search) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $searchTerm = "%{$search}%";
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            product_id,
            product_name,
            product_description,
            warranty,
            unit_price
        FROM products 
        WHERE product_id LIKE :search1 
           OR product_name LIKE :search2 
           OR product_description LIKE :search3
        ORDER BY 
            CASE 
                WHEN product_id LIKE :exact1 THEN 1
                WHEN product_name LIKE :exact2 THEN 2
                ELSE 3
            END,
            product_name ASC
        LIMIT 10
    ");
    
    $exactTerm = "{$search}%";
    
    $stmt->execute([
        'search1' => $searchTerm,
        'search2' => $searchTerm,
        'search3' => $searchTerm,
        'exact1' => $exactTerm,
        'exact2' => $exactTerm
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>