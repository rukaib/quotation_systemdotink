<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('quotation_list.php');
}

try {
    $pdo->beginTransaction();
    
    $quotation_id = intval($_POST['quotation_id']);
    $quotation_date = clean($_POST['quotation_date']);
    $customer_name = clean($_POST['customer_name']);
    $customer_phone = clean($_POST['customer_phone'] ?? '');
    $customer_email = clean($_POST['customer_email'] ?? '');
    $customer_address = clean($_POST['customer_address'] ?? '');
    $organization = clean($_POST['organization'] ?? '');
    $delivery_terms = clean($_POST['delivery_terms'] ?? '');
    $validity = clean($_POST['validity'] ?? '');
    $payment_terms = clean($_POST['payment_terms'] ?? '');
    $stock_availability = clean($_POST['stock_availability'] ?? '');
    $subtotal = floatval($_POST['subtotal']);
    $discount = floatval($_POST['discount'] ?? 0);
    $tax_amount = floatval($_POST['tax_amount']);
    $grand_total = floatval($_POST['grand_total']);
    $notes = clean($_POST['notes'] ?? '');
    $prepared_by = clean($_POST['prepared_by']);
    
    // Update quotation
    $stmt = $pdo->prepare("
        UPDATE quotations SET
            quotation_date = ?, customer_name = ?, customer_phone = ?,
            customer_email = ?, customer_address = ?, organization = ?,
            delivery_terms = ?, validity = ?, payment_terms = ?,
            stock_availability = ?, subtotal = ?, discount = ?,
            tax_amount = ?, grand_total = ?, notes = ?, prepared_by = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $quotation_date, $customer_name, $customer_phone, $customer_email,
        $customer_address, $organization, $delivery_terms, $validity,
        $payment_terms, $stock_availability, $subtotal, $discount,
        $tax_amount, $grand_total, $notes, $prepared_by, $quotation_id
    ]);
    
    // Delete old items
    $stmt = $pdo->prepare("DELETE FROM quotation_items WHERE quotation_id = ?");
    $stmt->execute([$quotation_id]);
    
    // Insert new items
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $itemOrder = 0;
        foreach ($_POST['items'] as $item) {
            if (empty(trim($item['name']))) continue;
            
            $itemOrder++;
            $item_name = clean($item['name']);
            $item_description = clean($item['description'] ?? '');
            $warranty = clean($item['warranty'] ?? '');
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['price']);
            $vat_amount = floatval($item['vat']);
            $line_total = floatval($item['total']);
            
            $stmt = $pdo->prepare("
                INSERT INTO quotation_items (
                    quotation_id, item_name, item_description, warranty,
                    quantity, unit_price, vat_amount, line_total, item_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $quotation_id, $item_name, $item_description, $warranty,
                $quantity, $unit_price, $vat_amount, $line_total, $itemOrder
            ]);
        }
    }
    
    $pdo->commit();
    
    setFlashMessage("Quotation updated successfully!", 'success');
    redirect("view_quotation.php?id={$quotation_id}");
    
} catch (Exception $e) {
    $pdo->rollBack();
    setFlashMessage("Error updating quotation: " . $e->getMessage(), 'error');
    redirect("edit_quotation.php?id={$quotation_id}");
}
?>