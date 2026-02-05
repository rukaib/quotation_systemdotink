<?php
/**
 * API: Search Customers by ID or Company Name
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
            customer_id,
            company_name,
            contact_person,
            phone,
            email,
            address
        FROM customers 
        WHERE customer_id LIKE :search1 
           OR company_name LIKE :search2 
           OR contact_person LIKE :search3
           OR phone LIKE :search4
        ORDER BY 
            CASE 
                WHEN customer_id LIKE :exact1 THEN 1
                WHEN company_name LIKE :exact2 THEN 2
                ELSE 3
            END,
            company_name ASC
        LIMIT 10
    ");
    
    $exactTerm = "{$search}%"; // Starts with search term
    
    $stmt->execute([
        'search1' => $searchTerm,
        'search2' => $searchTerm,
        'search3' => $searchTerm,
        'search4' => $searchTerm,
        'exact1' => $exactTerm,
        'exact2' => $exactTerm
    ]);
    
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($customers);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>