<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    redirect('quotation_list.php');
}

try {
    // Delete quotation (items will be deleted automatically via CASCADE)
    $stmt = $pdo->prepare("DELETE FROM quotations WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlashMessage("Quotation deleted successfully.", 'success');
    
} catch (Exception $e) {
    setFlashMessage("Error deleting quotation: " . $e->getMessage(), 'error');
}

redirect('quotation_list.php');
?>