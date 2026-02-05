<?php
/**
 * Helper Functions
 */

/**
 * Generate Auto Quotation Number
 * Format: QT-YYYY-XXXX
 * XXXX resets every year
 */
function generateQuotationNumber($pdo) {
    $currentYear = date('Y');
    $prefix = "QT-{$currentYear}-";
    
    try {
        // Get the last quotation number for current year
        $stmt = $pdo->prepare("
            SELECT quotation_no 
            FROM quotations 
            WHERE quotation_no LIKE :prefix 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastQuotation = $stmt->fetch();
        
        if ($lastQuotation) {
            // Extract number part and increment
            $lastNumber = (int) substr($lastQuotation['quotation_no'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            // First quotation of the year
            $newNumber = 1;
        }
        
        // Format: QT-2026-0001
        $quotationNo = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        // Check if exists (safety check)
        $stmt = $pdo->prepare("SELECT id FROM quotations WHERE quotation_no = ?");
        $stmt->execute([$quotationNo]);
        
        if ($stmt->fetch()) {
            // If exists, try next number (recursive)
            return generateQuotationNumber($pdo);
        }
        
        return $quotationNo;
        
    } catch (PDOException $e) {
        error_log("Error generating quotation number: " . $e->getMessage());
        return $prefix . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return number_format((float)$amount, 2, '.', ',');
}

/**
 * Sanitize input
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Convert number to words (for invoice)
 */
function numberToWords($number) {
    $ones = array(
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen'
    );
    
    $tens = array(
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    );
    
    $number = number_format($number, 2, '.', '');
    list($rupees, $cents) = explode('.', $number);
    
    $rupees = (int)$rupees;
    
    if ($rupees == 0) {
        return 'Zero Rupees Only';
    }
    
    $result = '';
    
    // Millions
    if ($rupees >= 1000000) {
        $millions = floor($rupees / 1000000);
        $result .= convertToWords($millions, $ones, $tens) . ' Million ';
        $rupees %= 1000000;
    }
    
    // Thousands
    if ($rupees >= 1000) {
        $thousands = floor($rupees / 1000);
        $result .= convertToWords($thousands, $ones, $tens) . ' Thousand ';
        $rupees %= 1000;
    }
    
    // Hundreds
    if ($rupees >= 100) {
        $hundreds = floor($rupees / 100);
        $result .= $ones[$hundreds] . ' Hundred ';
        $rupees %= 100;
    }
    
    // Remaining
    if ($rupees > 0) {
        $result .= convertToWords($rupees, $ones, $tens);
    }
    
    return trim($result) . ' Rupees Only';
}

function convertToWords($number, $ones, $tens) {
    if ($number < 20) {
        return $ones[$number];
    } elseif ($number < 100) {
        $ten = floor($number / 10);
        $one = $number % 10;
        return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
    }
    return '';
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        echo '<div class="alert ' . $alertClass[$type] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['flash_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
?>