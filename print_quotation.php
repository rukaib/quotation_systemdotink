<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    die('Invalid quotation ID');
}

$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute([$id]);
$quotation = $stmt->fetch();

if (!$quotation) {
    die('Quotation not found');
}

$stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY item_order");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Check if VAT is enabled
$vatEnabled = isset($quotation['vat_enabled']) ? (bool)$quotation['vat_enabled'] : true;

// Calculate items per page
$itemsPerFirstPage = 6;
$itemsPerNextPage = 12;

$totalItems = count($items);

// Determine how many pages we need (minimum 1 page)
if ($totalItems <= $itemsPerFirstPage) {
    $totalPages = 1;
} else {
    $remainingItems = $totalItems - $itemsPerFirstPage;
    $totalPages = 1 + ceil($remainingItems / $itemsPerNextPage);
}

// Ensure at least 1 page even with 0 items
$totalPages = max(1, $totalPages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?= htmlspecialchars($quotation['quotation_no']) ?></title>
    <style>
        /* ==========================================
           RESET & BASE
           ========================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --cyan: #00B0F0;
            --blue: #0070C0;
            --dark: #333;
        }

        html, body {
            font-family: Cambria, Georgia, serif;
            font-size: 10pt;
            line-height: 1.4;
            color: var(--dark);
            background: #fff;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

         .download-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #0070C0; /* Your brand blue */
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        z-index: 9999;
        transition: background 0.3s;
    }
    .download-btn:hover {
        background-color: #005a9e;
    }

        /* ==========================================
           A4 PAGE SETUP
           ========================================== */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* ==========================================
           PAGE CONTAINER - Fixed A4 Height
           ========================================== */
        .page {
            width: 210mm;
            height: 297mm;
            padding: 10mm 15mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* ==========================================
           HEADER
           ========================================== */
        .header {
            border-bottom: 2.5px solid var(--cyan);
            padding-bottom: 3mm;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 35%;
            vertical-align: middle;
        }

        .header-right {
            width: 65%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            max-width: 180px;
            height: auto;
        }

        .logo-text {
            font-size: 18pt;
            font-weight: bold;
            color: var(--blue);
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: var(--blue);
            margin-bottom: 1mm;
        }

        .company-details {
            font-size: 8pt;
            color: #555;
            line-height: 1.3;
        }

        /* ==========================================
           CONTENT AREA - Flexible
           ========================================== */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ==========================================
           TITLE
           ========================================== */
        .title {
            text-align: center;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .title h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
        }

        .page-indicator {
            font-size: 9pt;
            color: #666;
            margin-top: 1mm;
        }

        /* ==========================================
           INFO TABLE
           ========================================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 9pt;
            border: 1px solid #ccc;
            flex-shrink: 0;
        }

        .info-table td {
            padding: 1.5mm 2mm;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table .lbl {
            font-weight: 600;
            color: #555;
            width: 70px;
            white-space: nowrap;
        }

        .info-table .col {
            width: 5px;
        }

        .info-table .val {
            color: #000;
        }

        /* ==========================================
           TERMS TABLE
           ========================================== */
        .terms-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
            flex-shrink: 0;
        }

        .terms-table th,
        .terms-table td {
            border: 1px solid #999;
            padding: 1.5mm;
            text-align: center;
        }

        .terms-table th {
            background: #f0f0f0;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ==========================================
           ITEMS TABLE
           ========================================== */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
            border: 1px solid #000;
            flex-shrink: 0;
        }

        .items-table th {
            background: var(--cyan);
            color: #fff;
            padding: 2mm 1.5mm;
            text-align: center;
            font-weight: 600;
            font-size: 8pt;
            text-transform: uppercase;
            border: 1px solid #000;
        }

        .items-table td {
            padding: 1.5mm 1.5mm;
            border: 1px solid #000;
            vertical-align: top;
        }

        .items-table .txt-c { text-align: center; }
        .items-table .txt-r { text-align: right; }

        .prd-name {
            font-weight: 600;
            font-size: 8pt;
        }

        .prd-desc {
            font-size: 7pt;
            color: #555;
            margin-top: 0.5mm;
            line-height: 1.2;
        }

        /* Continued indicator */
        .continued-note {
            text-align: center;
            font-size: 8pt;
            color: #666;
            font-style: italic;
            padding: 2mm;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        /* ==========================================
           TOTALS
           ========================================== */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-left {
            width: 55%;
            vertical-align: top;
            padding-right: 3mm;
        }

        .totals-right {
            width: 45%;
            vertical-align: top;
        }

        .words-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 2mm;
            font-size: 8pt;
            font-style: italic;
            line-height: 1.3;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .amounts-table td {
            padding: 1mm 2mm;
        }

        .amounts-table .txt-r {
            text-align: right;
        }

        .amounts-table .total-row td {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            font-weight: bold;
            font-size: 10pt;
            padding: 1.5mm 2mm;
        }

        /* ==========================================
           NOTES
           ========================================== */
        .notes-box {
            border: 1px solid var(--cyan);
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .notes-title {
            background: var(--cyan);
            color: #fff;
            padding: 1.5mm;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .notes-content {
            padding: 2mm;
            font-size: 8pt;
            line-height: 1.3;
        }

        /* ==========================================
           FOOTER - Always at Bottom
           ========================================== */
        .footer {
            margin-top: auto;
            flex-shrink: 0;
            padding-top: 2mm;
        }

        .footer-contact {
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }

        .footer-contact-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-contact-left {
            width: 70%;
            vertical-align: top;
            font-size: 8pt;
            line-height: 1.3;
        }

        .footer-contact-right {
            width: 30%;
            text-align: right;
            vertical-align: top;
        }

        .footer-contact-name {
            color: var(--blue);
            font-weight: bold;
            font-size: 9pt;
        }

        .footer-bottom {
            margin-top: auto;
        }

        .footer-line {
            border-top: 1.5px solid var(--cyan);
            margin: 0;
        }

        .footer-copyright {
            text-align: center;
            font-size: 7pt;
            color: #777;
            padding-top: 1.5mm;
        }

        /* ==========================================
           PRINT STYLES
           ========================================== */
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
            }
            

            @media print {
            .page{
                width: 210mm;
                min-height: 297mm;   /* not height */
                padding: 10mm 15mm;
                margin: 0;
                break-after: page;
                page-break-after: always;
            }
            .page:last-child{
                break-after: auto;
                page-break-after: auto;
            }
        }

            /* Avoid breaking inside table rows */
            .items-table tr {
                page-break-inside: avoid;
            }

            /* Color fixes for print */
            .items-table th {
                background: var(--cyan) !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
            }

            .terms-table th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }

            .notes-title {
                background: var(--cyan) !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
            }

            .words-box {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
            }

            .footer-line {
                border-top-color: var(--cyan) !important;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        /* ==========================================
           SCREEN PREVIEW
           ========================================== */
        @media screen {
            body {
                background: #555;
                padding: 10mm;
            }

            .page {
                box-shadow: 0 0 20px rgba(0,0,0,0.4);
                margin-bottom: 10mm;
            }
        }

        /* Print button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--blue);
            color: #fff;
            border: none;
            padding: 10px 25px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .print-btn:hover {
            background: #005a9e;
        }
    </style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">
    🖨️ Print Quotation
</button>

<?php
// Split items into pages
$itemIndex = 0;
$currentPage = 1;

// Only loop if we have items, otherwise just show 1 page
$hasMoreItems = ($totalItems > 0);

while ($currentPage <= $totalPages):
    // Determine how many items for this page
    if ($currentPage === 1) {
        $itemsThisPage = min($itemsPerFirstPage, $totalItems);
    } else {
        $itemsThisPage = min($itemsPerNextPage, $totalItems - $itemIndex);
    }
    
    // Get items for this page
    $pageItems = array_slice($items, $itemIndex, $itemsThisPage);
    $itemIndex += $itemsThisPage;
    
    // Is this the last page?
    $isLastPage = ($currentPage >= $totalPages);
    $isFirstPage = ($currentPage === 1);
?>

<div class="page">
    <!-- ========================================
         HEADER (on every page)
         ======================================== -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <?php if (file_exists('assets/images/logo.png')): ?>
                        <img src="assets/images/logo.png" alt="Logo" class="logo-img">
                    <?php else: ?>
                        <div class="logo-text">DOT INK</div>
                    <?php endif; ?>
                </td>
                <td class="header-right">
                    <div class="company-name">DOT INK (PVT) LTD</div>
                    <div class="company-details">
                        218/13A, Ranimadama, Enderamulla, Wattala.<br>
                        Hotline: 075 966 166 8 | General: 011 368 780<br>
                        Email: info@dotink.lk | www.dotink.lk | VAT: 178769677-7000
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- CONTENT AREA -->
    <div class="content">
        <!-- TITLE -->
        <div class="title">
            <h1>QUOTATION</h1>
            <?php if ($totalPages > 1): ?>
                <div class="page-indicator">Page <?= $currentPage ?> of <?= $totalPages ?></div>
            <?php endif; ?>
        </div>

        <?php if ($isFirstPage): ?>
            <!-- CUSTOMER INFO (only on first page) -->
            <table class="info-table">
                <tr>
                    <td class="lbl">Attention</td>
                    <td class="col">:</td>
                    <td class="val"><strong><?= htmlspecialchars($quotation['contact_person'] ?: '-') ?></strong></td>
                    <td class="lbl">Quotation No</td>
                    <td class="col">:</td>
                    <td class="val"><strong style="color:var(--blue)"><?= htmlspecialchars($quotation['quotation_no']) ?></strong></td>
                </tr>
                <tr>
                    <td class="lbl">Company</td>
                    <td class="col">:</td>
                    <td class="val"><strong><?= htmlspecialchars($quotation['company_name'] ?: '-') ?></strong></td>
                    <td class="lbl">Date</td>
                    <td class="col">:</td>
                    <td class="val"><?= date('F d, Y', strtotime($quotation['quotation_date'])) ?></td>
                </tr>
                <tr>
                    <td class="lbl">Customer ID</td>
                    <td class="col">:</td>
                    <td class="val"><?= htmlspecialchars($quotation['customer_id'] ?: '-') ?></td>
                    <td class="lbl">Phone</td>
                    <td class="col">:</td>
                    <td class="val"><?= htmlspecialchars($quotation['phone'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="lbl">Address</td>
                    <td class="col">:</td>
                    <td class="val"><?= htmlspecialchars($quotation['address'] ?: '-') ?></td>
                    <td class="lbl">Email</td>
                    <td class="col">:</td>
                    <td class="val"><?= htmlspecialchars($quotation['email'] ?: '-') ?></td>
                </tr>
            </table>

            <!-- TERMS (only on first page) -->
            <table class="terms-table">
                <tr>
                    <th>Delivery</th>
                    <th>Validity</th>
                    <th>Payments</th>
                    <th>Stock</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($quotation['delivery_terms'] ?: 'Within 2 Days') ?></td>
                    <td><?= htmlspecialchars($quotation['validity'] ?: '100 Days') ?></td>
                    <td><?= htmlspecialchars($quotation['payment_terms'] ?: '30 Days Credit') ?></td>
                    <td><?= htmlspecialchars($quotation['stock_availability'] ?: 'Available') ?></td>
                </tr>
            </table>
        <?php else: ?>
            <!-- Continuation note on subsequent pages -->
            <div class="continued-note">
                <strong>Quotation No: <?= htmlspecialchars($quotation['quotation_no']) ?></strong> — 
                <?= htmlspecialchars($quotation['company_name']) ?> — Items Continued...
            </div>
        <?php endif; ?>

        <!-- ITEMS TABLE -->
        <?php if (count($pageItems) > 0): ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:10%">Product ID</th>
                    <th style="width:<?= $vatEnabled ? '29%' : '37%' ?>">Product / Specification</th>
                    <th style="width:9%">Warranty</th>
                    <th style="width:6%">Qty</th>
                    <th style="width:12%">Unit Price</th>
                    <?php if ($vatEnabled): ?>
                        <th style="width:11%">VAT 18%</th>
                    <?php endif; ?>
                    <th style="width:13%">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Calculate starting row number
                if ($currentPage === 1) {
                    $rowNumber = 1;
                } else {
                    $rowNumber = $itemsPerFirstPage + (($currentPage - 2) * $itemsPerNextPage) + 1;
                }
                
                foreach ($pageItems as $item): 
                ?>
                    <tr>
                        <td class="txt-c"><?= $rowNumber++ ?></td>
                        <td class="txt-c"><strong><?= htmlspecialchars($item['product_id'] ?: '-') ?></strong></td>
                        <td>
                            <div class="prd-name"><?= htmlspecialchars($item['product_name']) ?></div>
                            <?php if (!empty($item['product_description'])): ?>
                                <div class="prd-desc"><?= nl2br(htmlspecialchars($item['product_description'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="txt-c"><?= htmlspecialchars($item['warranty'] ?: '-') ?></td>
                        <td class="txt-c"><?= intval($item['quantity']) ?></td>
                        <td class="txt-r">Rs. <?= number_format($item['unit_price'], 2) ?></td>
                        <?php if ($vatEnabled): ?>
                            <td class="txt-r">Rs. <?= number_format($item['vat_amount'], 2) ?></td>
                        <?php endif; ?>
                        <td class="txt-r"><strong>Rs. <?= number_format($item['line_total'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($isFirstPage): ?>
            <!-- No items message on first page -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:10%">Product ID</th>
                        <th style="width:<?= $vatEnabled ? '29%' : '37%' ?>">Product / Specification</th>
                        <th style="width:9%">Warranty</th>
                        <th style="width:6%">Qty</th>
                        <th style="width:12%">Unit Price</th>
                        <?php if ($vatEnabled): ?>
                            <th style="width:11%">VAT 18%</th>
                        <?php endif; ?>
                        <th style="width:13%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="<?= $vatEnabled ? 8 : 7 ?>" class="txt-c" style="padding:5mm;color:#999;">
                            No items in this quotation
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!$isLastPage): ?>
            <!-- Continued on next page indicator -->
            <div class="continued-note">
                Continued on next page...
            </div>
        <?php endif; ?>

        <?php if ($isLastPage): ?>
            <!-- TOTALS (only on last page) -->
            <div class="totals-wrapper">
                <table class="totals-table">
                    <tr>
                        <td class="totals-left">
                            <div class="words-box">
                                <strong>Amount in Words:</strong><br>
                                <?= numberToWords($quotation['grand_total']) ?>
                            </div>
                        </td>
                        <td class="totals-right">
                            <table class="amounts-table">
                                <tr>
                                    <td><strong>Sub Total</strong></td>
                                    <td class="txt-r"><strong>Rs. <?= number_format($quotation['subtotal'], 2) ?></strong></td>
                                </tr>
                                <?php if ($quotation['discount'] > 0): ?>
                                    <tr>
                                        <td>Discount</td>
                                        <td class="txt-r">- Rs. <?= number_format($quotation['discount'], 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($vatEnabled && $quotation['tax_amount'] > 0): ?>
                                    <tr>
                                        <td>VAT (18%)</td>
                                        <td class="txt-r">Rs. <?= number_format($quotation['tax_amount'], 2) ?></td>
                                    </tr>   
                                <?php endif; ?>
                                <tr class="total-row">
                                    <td><strong>GRAND TOTAL</strong></td>
                                    <td class="txt-r"><strong>Rs. <?= number_format($quotation['grand_total'], 2) ?></strong></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- NOTES (only on last page) -->
            <?php if (!empty($quotation['notes'])): ?>
                <div class="notes-box">
                    <div class="notes-title">Special Notes</div>
                    <div class="notes-content">
                        <?= nl2br(htmlspecialchars($quotation['notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ========================================
         FOOTER (on every page - always at bottom)
         ======================================== -->
    <div class="footer">
        <div class="footer-contact">
            <table class="footer-contact-table">
                <tr>
                    <td class="footer-contact-left">
                        <strong>For Sales and Technical Inquiries:</strong><br>
                        <span class="footer-contact-name"><?= htmlspecialchars($quotation['prepared_by'] ?: 'M.ASHAN') ?></span><br>
                        Executive - Operation Management | Mobile: 075 966 166 8
                    </td>
                    <td class="footer-contact-right">
                        <strong style="color:var(--blue);"><?= htmlspecialchars($quotation['quotation_no']) ?></strong>
                        <?php if ($totalPages > 1): ?>
                            <br><span style="font-size:8pt;color:#666;">Page <?= $currentPage ?>/<?= $totalPages ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-bottom">
            <div class="footer-line"></div>
            <div class="footer-copyright">
                © DOT INK (PVT) LTD • This Document is Copyright to DOT INK (PVT) LTD
            </div>
        </div>
    </div>
</div>

<?php
    $currentPage++;
endwhile;
?>

</body>
</html>