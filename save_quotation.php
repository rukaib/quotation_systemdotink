<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('create_quotation.php');
}

try {
    $pdo->beginTransaction();
    
    // CUSTOMER
    $customer_ref_id = intval($_POST['customer_ref_id'] ?? 0);
    $customer_id = clean($_POST['customer_id'] ?? '');
    $company_name = clean($_POST['company_name'] ?? '');
    $contact_person = clean($_POST['contact_person'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $address = clean($_POST['address'] ?? '');
    
    // Create new customer if needed
    if (($customer_ref_id === 0 || $customer_id === 'NEW') && !empty($company_name)) {
        $stmt = $pdo->prepare("SELECT id, customer_id FROM customers WHERE company_name = ? LIMIT 1");
        $stmt->execute([$company_name]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $customer_ref_id = $existing['id'];
            $customer_id = $existing['customer_id'];
            $stmt = $pdo->prepare("UPDATE customers SET 
                contact_person = COALESCE(NULLIF(?, ''), contact_person),
                phone = COALESCE(NULLIF(?, ''), phone),
                email = COALESCE(NULLIF(?, ''), email),
                address = COALESCE(NULLIF(?, ''), address)
                WHERE id = ?");
            $stmt->execute([$contact_person, $phone, $email, $address, $customer_ref_id]);
        } else {
            $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(customer_id, 6) AS UNSIGNED)) as max_num FROM customers WHERE customer_id LIKE 'CUST-%'");
            $result = $stmt->fetch();
            $nextNum = ($result['max_num'] ?? 0) + 1;
            $customer_id = 'CUST-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("INSERT INTO customers (customer_id, company_name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customer_id, $company_name, $contact_person, $phone, $email, $address]);
            $customer_ref_id = $pdo->lastInsertId();
        }
    }
    
    // QUOTATION
    $quotation_no = clean($_POST['quotation_no'] ?? '');
    $quotation_date = clean($_POST['quotation_date'] ?? date('Y-m-d'));
    $delivery_terms = clean($_POST['delivery_terms'] ?? 'Within 2 Days after Confirmation');
    $validity = clean($_POST['validity'] ?? '100 Days');
    $payment_terms = clean($_POST['payment_terms'] ?? '30 Days Credit / COD');
    $stock_availability = clean($_POST['stock_availability'] ?? 'Available');
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $discount = floatval($_POST['discount'] ?? 0);
    $tax_amount = floatval($_POST['tax_amount'] ?? 0);
    $grand_total = floatval($_POST['grand_total'] ?? 0);
    $notes = clean($_POST['notes'] ?? '');
    $prepared_by = clean($_POST['prepared_by'] ?? 'M.ASHAN');
    
    // VAT ENABLED (checkbox)
    $vat_enabled = isset($_POST['vat_enabled']) ? 1 : 0;
    
    $stmt = $pdo->prepare("INSERT INTO quotations (
        quotation_no, quotation_date, customer_ref_id, customer_id,
        company_name, contact_person, phone, email, address,
        delivery_terms, validity, payment_terms, stock_availability,
        subtotal, discount, vat_enabled, tax_amount, grand_total, notes, prepared_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $quotation_no, $quotation_date, $customer_ref_id ?: null, $customer_id,
        $company_name, $contact_person, $phone, $email, $address,
        $delivery_terms, $validity, $payment_terms, $stock_availability,
        $subtotal, $discount, $vat_enabled, $tax_amount, $grand_total, $notes, $prepared_by
    ]);
    
    $quotation_id = $pdo->lastInsertId();
    
    // ITEMS
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        $order = 0;
        
        foreach ($_POST['items'] as $item) {
            $product_name = clean($item['product_name'] ?? '');
            if (empty($product_name)) continue;
            
            $order++;
            $product_ref_id = intval($item['product_ref_id'] ?? 0);
            $product_id = clean($item['product_id'] ?? '');
            $product_description = clean($item['product_description'] ?? '');
            $warranty = clean($item['warranty'] ?? '');
            $quantity = intval($item['quantity'] ?? 1);
            $unit_price = floatval($item['unit_price'] ?? 0);
            $vat_amount = floatval($item['vat_amount'] ?? 0);
            $line_total = floatval($item['line_total'] ?? 0);
            
            // Create new product if needed
            if (($product_ref_id === 0 || $product_id === 'NEW') && !empty($product_name)) {
                $stmt = $pdo->prepare("SELECT id, product_id FROM products WHERE product_name = ? LIMIT 1");
                $stmt->execute([$product_name]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $product_ref_id = $existing['id'];
                    $product_id = $existing['product_id'];
                    $stmt = $pdo->prepare("UPDATE products SET 
                        product_description = COALESCE(NULLIF(?, ''), product_description),
                        warranty = COALESCE(NULLIF(?, ''), warranty),
                        unit_price = ?
                        WHERE id = ?");
                    $stmt->execute([$product_description, $warranty, $unit_price, $product_ref_id]);
                } else {
                    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(product_id, 5) AS UNSIGNED)) as max_num FROM products WHERE product_id LIKE 'PRD-%'");
                    $result = $stmt->fetch();
                    $nextNum = ($result['max_num'] ?? 0) + 1;
                    $product_id = 'PRD-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                    
                    $stmt = $pdo->prepare("INSERT INTO products (product_id, product_name, product_description, warranty, unit_price) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$product_id, $product_name, $product_description, $warranty, $unit_price]);
                    $product_ref_id = $pdo->lastInsertId();
                }
            }
            
            // Insert item
            $stmt = $pdo->prepare("INSERT INTO quotation_items (
                quotation_id, product_ref_id, product_id, product_name,
                product_description, warranty, quantity, unit_price,
                vat_amount, line_total, item_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $quotation_id, $product_ref_id ?: null, $product_id, $product_name,
                $product_description, $warranty, $quantity, $unit_price,
                $vat_amount, $line_total, $order
            ]);
        }
    }
    
    $pdo->commit();
    
    setFlashMessage("Quotation {$quotation_no} saved successfully!", 'success');
    redirect("view_quotation.php?id={$quotation_id}");
    
} catch (Exception $e) {
    $pdo->rollBack();
    setFlashMessage("Error: " . $e->getMessage(), 'error');
    redirect('create_quotation.php');
}
?>