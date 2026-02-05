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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?= htmlspecialchars($quotation['quotation_no']) ?></title>
    <style>
        /* ==========================================
           RESET
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
            font-family: Cambria, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: var(--dark);
            background: #fff;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ==========================================
           A4 PAGE SETUP - SINGLE PAGE ONLY
        ========================================== */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* ==========================================
           PAGE CONTAINER - EXACT A4 SIZE
        ========================================== */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            padding: 12mm 15mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ==========================================
           HEADER
        ========================================== */
        .header {
            border-bottom: 2.5px solid var(--cyan);
            padding-bottom: 3mm;
            margin-bottom: 4mm;
            flex-shrink: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 30%;
            vertical-align: middle;
        }

        .header-right {
            width: 70%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            width: 350px;
            height: auto;
        }

        .logo-text {
            font-size: 20pt;
            font-weight: bold;
            color: var(--blue);
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: var(--blue);
            margin-bottom: 2mm;
        }

        .company-details {
            font-size: 9pt;
            color: #555;
            line-height: 1.4;
        }

        /* ==========================================
           MAIN CONTENT
        ========================================== */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-inner {
            flex: 1;
            overflow: hidden;
        }

        /* ==========================================
           TITLE
        ========================================== */
        .title {
            text-align: center;
            margin-bottom: 4mm;
        }

        .title h1 {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
        }

        .vat-badge {
            display: inline-block;
            padding: 1mm 3mm;
            border-radius: 2px;
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1mm;
        }

        .vat-badge.included {
            background: #d4edda;
            color: #155724;
        }

        .vat-badge.excluded {
            background: #f8d7da;
            color: #721c24;
        }

        /* ==========================================
           INFO TABLE
        ========================================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 9pt;
            border: 1px solid #ccc;
        }

        .info-table td {
            padding: 2mm 3mm;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table .lbl {
            font-weight: 600;
            color: #555;
            width: 80px;
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
            margin-bottom: 4mm;
            font-size: 9pt;
        }

        .terms-table th,
        .terms-table td {
            border: 1px solid #999;
            padding: 2mm;
            text-align: center;
        }

        .terms-table th {
            background: #f0f0f0;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
        }

        /* ==========================================
           ITEMS TABLE
        ========================================== */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 9pt;
            border: 1px solid #000;
        }

        .items-table th {
            background: var(--cyan);
            color: #fff;
            padding: 2mm 1.5mm;
            text-align: center;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            border: 1px solid #000;
        }

        .items-table td {
            padding: 2mm 1.5mm;
            border: 1px solid #000;
            vertical-align: top;
        }

        .items-table .txt-c { text-align: center; }
        .items-table .txt-r { text-align: right; }

        .prd-name {
            font-weight: 600;
            font-size: 9pt;
        }

        .prd-desc {
            font-size: 8pt;
            color: #555;
            margin-top: 1mm;
            line-height: 1.3;
            max-height: 15mm;
            overflow: hidden;
        }

        /* ==========================================
           TOTALS
        ========================================== */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 4mm;
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
            padding: 3mm;
            font-size: 9pt;
            font-style: italic;
            line-height: 1.4;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .amounts-table td {
            padding: 1.5mm 2mm;
        }

        .amounts-table .txt-r {
            text-align: right;
        }

        .amounts-table .total-row td {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            font-weight: bold;
            font-size: 11pt;
            padding: 2mm;
        }

        /* ==========================================
           NOTES
        ========================================== */
        .notes-box {
            border: 1px solid var(--cyan);
            margin-bottom: 4mm;
            padding-bottom: 8mm;
        }

        .notes-title {
            background: var(--cyan);
            color: #fff;
            padding: 2mm;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .notes-content {
            padding: 3mm;
            font-size: 9pt;
            line-height: 1.4;
            max-height: 20mm;
            overflow: hidden;
        }

        /* ==========================================
           FOOTER - RESTRUCTURED
        ========================================== */
        .footer {
            margin-top: auto;
            flex-shrink: 0;
        }

        /* Contact section - ABOVE the line */
        .footer-contact {
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .footer-contact-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-contact-left {
            width: 65%;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.4;
        }

        .footer-contact-right {
            width: 35%;
            text-align: right;
            vertical-align: top;
        }

        .footer-contact-name {
            color: var(--blue);
            font-weight: bold;
            font-size: 10pt;
        }

        .footer-quotation-no {
            font-weight: bold;
            color: var(--blue);
            font-size: 9pt;
        }

        /* Footer line */
        .footer-line {
            border-top: 1.5px solid var(--cyan);
            margin: 0;
        }

        /* Copyright section - BELOW the line, CENTERED */
        .footer-copyright {
            text-align: center;
            font-size: 8pt;
            color: #777;
            padding-top: 2mm;
        }

        /* ==========================================
           PRINT STYLES - SINGLE PAGE ONLY
        ========================================== */
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            .page {
                width: 210mm;
                height: 297mm;
                min-height: auto;
                max-height: 297mm;
                padding: 12mm 15mm;
                margin: 0;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
            }

            /* Force no second page */
            html::after,
            body::after,
            .page::after {
                display: none !important;
                content: none !important;
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

            .vat-badge.included {
                background: #d4edda !important;
                -webkit-print-color-adjust: exact;
            }

            .vat-badge.excluded {
                background: #f8d7da !important;
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
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- ========================================
             HEADER
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

        <!-- ========================================
             MAIN CONTENT
        ======================================== -->
        <div class="content">
            <div class="content-inner">
                <!-- TITLE -->
                <div class="title">
                    <h1>QUOTATION</h1>
                    <?php if ($vatEnabled): ?>
                        <!-- <span class="vat-badge included">VAT INCLUDED (18%)</span> -->
                    <?php else: ?>
                        <!-- <span class="vat-badge excluded">VAT EXEMPT</span> -->
                    <?php endif; ?>
                </div>

                <!-- CUSTOMER INFO -->
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

                <!-- TERMS -->
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

                
                <!-- ITEMS TABLE -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:11%">Product ID</th>
                            <th style="width:<?= $vatEnabled ? '31%' : '39%' ?>">Product / Specification</th>
                            <th style="width:10%">Warranty</th>
                            <th style="width:7%">Qty</th>
                            <th style="width:13%">Unit Price</th>
                            <?php if ($vatEnabled): ?>
                            <th style="width:12%">VAT 18%</th>
                            <?php endif; ?>
                            <th style="width:14%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
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
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $vatEnabled ? 7 : 6 ?>" class="txt-c" style="padding:3mm">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- TOTALS -->
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

                <!-- NOTES -->
                <?php if (!empty($quotation['notes'])): ?>
                <div class="notes-box">
                    <div class="notes-title">Special Notes</div>
                    <div class="notes-content">
                        <?= nl2br(htmlspecialchars($quotation['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================
             FOOTER - RESTRUCTURED
        ======================================== -->
        <div class="footer">
            <!-- Contact Info - ABOVE the line -->
            <div class="footer-contact">
                <table class="footer-contact-table">
                    <tr>
                        <td class="footer-contact-left">
                            <strong>For Sales and Technical Inquiries:</strong><br>
                            <span class="footer-contact-name"><?= htmlspecialchars($quotation['prepared_by'] ?: 'M.ASHAN') ?></span><br>
                            Executive - Operation Management | Mobile: 075 966 166 8
                        </td>
                        <!-- <td class="footer-contact-right">
                            <div class="footer-quotation-no">
                                <?= htmlspecialchars($quotation['quotation_no']) ?>
                            </div>
                        </td> -->
                    </tr>
                </table>
            </div>

            <!-- Footer Line -->
            <div class="footer-line"></div>

            <!-- Copyright - BELOW the line, CENTERED -->
            <div class="footer-copyright">
                © DOT INK (PVT) LTD • This Document is Copyright to DOT INK (PVT) LTD
            </div>
        </div>
    </div>
</body>
</html>