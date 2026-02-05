<?php
/**
 * API: Search Customers by Customer ID or Company Name
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
    // Search by customer_id OR company_name
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
        WHERE customer_id LIKE :search 
           OR company_name LIKE :search 
           OR contact_person LIKE :search
           OR phone LIKE :search
        ORDER BY company_name ASC
        LIMIT 15
    ");
    
    $searchTerm = "%{$search}%";
    $stmt->execute(['search' => $searchTerm]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($customers);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>