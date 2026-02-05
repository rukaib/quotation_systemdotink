-- =====================================================
-- QUOTATION MANAGEMENT SYSTEM - COMPLETE DATABASE
-- UPDATED: Search by Company Name + Customer/Product ID
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS quotation_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE quotation_system;

-- Drop existing tables (in correct order due to foreign keys)
DROP TABLE IF EXISTS quotation_items;
DROP TABLE IF EXISTS quotations;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS customers;

-- =====================================================
-- TABLE 1: CUSTOMERS (Search by Company/Organization)
-- =====================================================
CREATE TABLE customers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(20) NOT NULL UNIQUE,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_customer_id (customer_id),
    INDEX idx_company_name (company_name),
    INDEX idx_contact_person (contact_person),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 2: PRODUCTS (Search by Product ID & Name)
-- =====================================================
CREATE TABLE products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(20) NOT NULL UNIQUE,
    product_name VARCHAR(500) NOT NULL,
    product_description TEXT DEFAULT NULL,
    warranty VARCHAR(255) DEFAULT NULL,
    unit_price DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_product_id (product_id),
    INDEX idx_product_name (product_name),
    INDEX idx_unit_price (unit_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 3: QUOTATIONS
-- =====================================================
CREATE TABLE quotations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    quotation_no VARCHAR(50) NOT NULL UNIQUE,
    quotation_date DATE NOT NULL,
    
    -- Customer Reference (using customer_id)
    customer_ref_id INT(11) DEFAULT NULL,
    customer_id VARCHAR(20) DEFAULT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    
    -- Terms & Conditions
    delivery_terms VARCHAR(255) DEFAULT 'Within 2 Days after Confirmation',
    validity VARCHAR(100) DEFAULT '100 Days',
    payment_terms VARCHAR(255) DEFAULT '30 Days Credit / COD',
    stock_availability VARCHAR(100) DEFAULT 'Available',
    
    -- Financial Information
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15, 2) DEFAULT 0.00,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    tax_rate DECIMAL(5, 2) DEFAULT 18.00,
    tax_amount DECIMAL(15, 2) DEFAULT 0.00,
    grand_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    
    -- Additional Information
    notes TEXT DEFAULT NULL,
    prepared_by VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_quotation_no (quotation_no),
    INDEX idx_quotation_date (quotation_date),
    INDEX idx_customer_id (customer_id),
    INDEX idx_company_name (company_name),
    INDEX idx_status (status),
    
    FOREIGN KEY (customer_ref_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 4: QUOTATION ITEMS
-- =====================================================
CREATE TABLE quotation_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT(11) NOT NULL,
    
    -- Product Reference (using product_id)
    product_ref_id INT(11) DEFAULT NULL,
    product_id VARCHAR(20) DEFAULT NULL,
    product_name VARCHAR(500) NOT NULL,
    product_description TEXT DEFAULT NULL,
    warranty VARCHAR(255) DEFAULT NULL,
    
    -- Quantity & Pricing
    quantity INT(11) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    vat_amount DECIMAL(15, 2) DEFAULT 0.00,
    line_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    
    -- Order
    item_order INT(11) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_quotation_id (quotation_id),
    INDEX idx_product_id (product_id),
    INDEX idx_product_name (product_name),
    
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_ref_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA: CUSTOMERS (with customer_id)
-- =====================================================
INSERT INTO customers (customer_id, company_name, contact_person, phone, email, address) VALUES
('CUST-001', 'Lanka Tiles PLC', 'Mr. Supun Nirman', '114 526 700', 'supun@lankatiles.com', 'Lanka Tiles 215, Nawala Road, Colombo'),
('CUST-002', 'ABC Company (Pvt) Ltd', 'John Perera', '077 123 4567', 'john@abccompany.lk', '123 Galle Road, Colombo 03'),
('CUST-003', 'XYZ Holdings', 'Mary Silva', '011 234 5678', 'mary@xyzholdings.lk', '45 Main Street, Kandy'),
('CUST-004', 'Tech Solutions Lanka', 'Kamal Fernando', '077 987 6543', 'kamal@techsolutions.lk', '78 Temple Road, Nugegoda'),
('CUST-005', 'Global Enterprises', 'Nimal Bandara', '011 567 8901', 'nimal@globalent.lk', '234 High Level Road, Maharagama'),
('CUST-006', 'Digital Systems (Pvt) Ltd', 'Priya Jayawardena', '076 234 5678', 'priya@digitalsys.lk', '56 Duplication Road, Colombo 04'),
('CUST-007', 'Office World Lanka', 'Saman Kumara', '011 345 6789', 'saman@officeworld.lk', '89 Baseline Road, Colombo 09'),
('CUST-008', 'Smart Business Solutions', 'Chamari Perera', '077 456 7890', 'chamari@smartbiz.lk', '12 Park Road, Colombo 05'),
('CUST-009', 'IT Hub Lanka', 'Ruwan Dissanayake', '076 567 8901', 'ruwan@ithub.lk', '345 Kandy Road, Kelaniya'),
('CUST-010', 'Creative Media Agency', 'Dilini Fernando', '077 678 9012', 'dilini@creativemedia.lk', '67 Flower Road, Colombo 07'),
('CUST-011', 'Hayleys PLC', 'Amitha Gunawardena', '011 2627000', 'amitha@hayleys.com', '400 Deans Road, Colombo 10'),
('CUST-012', 'John Keells Holdings', 'Prasad Mendis', '011 2306000', 'prasad@jkh.lk', '117 Sir Chittampalam A Gardiner Mawatha, Colombo 02'),
('CUST-013', 'Dialog Axiata PLC', 'Suren Perera', '077 7678678', 'suren@dialog.lk', '475 Union Place, Colombo 02'),
('CUST-014', 'MAS Holdings', 'Chandrika Silva', '011 2506000', 'chandrika@mas.lk', 'MAS Fabric Park, Thulhiriya'),
('CUST-015', 'Cargills Ceylon PLC', 'Ranjith Kumar', '011 2426000', 'ranjith@cargills.lk', '40 York Street, Colombo 01');

-- =====================================================
-- SAMPLE DATA: PRODUCTS (with product_id)
-- =====================================================
INSERT INTO products (product_id, product_name, product_description, warranty, unit_price) VALUES
('PRD-001', 'DELL INSPIRON 3030 i3 14 GEN DESKTOP', 'Intel Core i3 – 14100 14GEN Processor\n512GB M.2 PCIe NVMe SSD\n8GB DDR5 5600 MHz RAM\nDell 24 Monitor SE2425HM 23.8-inch Full HD\nDell Optical Mouse MS116 & Dell KB216 USB Keyboard\nWINDOWS 11 PRO GENUINE', '3 Years On-Site', 185000.00),
('PRD-002', 'DELL INSPIRON 3030 i5 14 GEN DESKTOP', 'Intel Core i5 – 14400 14GEN Processor\n512GB M.2 PCIe NVMe SSD\n16GB DDR5 5600 MHz RAM\nDell 24 Monitor SE2425HM 23.8-inch Full HD\nDell Optical Mouse MS116 & Dell KB216 USB Keyboard\nWINDOWS 11 PRO GENUINE', '3 Years On-Site', 245000.00),
('PRD-003', 'DELL INSPIRON 3030 i7 14 GEN DESKTOP', 'Intel Core i7 – 14700 14GEN Processor\n1TB M.2 PCIe NVMe SSD\n32GB DDR5 5600 MHz RAM\nDell 27 Monitor Full HD IPS\nDell Optical Mouse MS116 & Dell KB216 USB Keyboard\nWINDOWS 11 PRO GENUINE', '3 Years On-Site', 385000.00),
('PRD-004', 'HP LaserJet Pro M404dn Printer', 'Laser Printer\nDuplex Printing\nNetwork Ready (Ethernet)\nPrint Speed: 40 ppm\nMonthly Duty Cycle: 80,000 pages', '1 Year', 75000.00),
('PRD-005', 'HP LaserJet Pro MFP M428fdw', 'All-in-One Laser Printer\nPrint, Scan, Copy, Fax\nDuplex Printing\nWireless & Ethernet\nPrint Speed: 40 ppm', '1 Year', 125000.00),
('PRD-006', 'Canon PIXMA G3020 All-in-One', 'All-in-One Ink Tank Printer\nPrint, Scan, Copy\nWireless Connectivity\nHigh Yield Ink Bottles\nBorderless Printing', '2 Years', 35000.00),
('PRD-007', 'Canon PIXMA G6070 All-in-One', 'All-in-One Ink Tank Printer\nPrint, Scan, Copy\nDuplex Printing\nWireless & Ethernet\nAuto Document Feeder', '2 Years', 55000.00),
('PRD-008', 'Epson EcoTank L3250 All-in-One', 'All-in-One Ink Tank Printer\nPrint, Scan, Copy\nWi-Fi Direct\nHigh Yield Ink Bottles\nBorderless Printing', '2 Years', 38000.00),
('PRD-009', 'Samsung 24" Monitor LS24F350FHW', '24-inch Full HD (1920x1080)\nIPS Panel\nHDMI & VGA Ports\n75Hz Refresh Rate\nAMD FreeSync', '3 Years', 28000.00),
('PRD-010', 'Samsung 27" Monitor LS27F350FHW', '27-inch Full HD (1920x1080)\nIPS Panel\nHDMI & VGA Ports\n75Hz Refresh Rate\nAMD FreeSync', '3 Years', 38000.00),
('PRD-011', 'Dell 24 Monitor SE2422H', '23.8-inch Full HD (1920x1080)\nVA Panel\nHDMI & VGA Ports\n75Hz Refresh Rate\nAMD FreeSync', '3 Years', 32000.00),
('PRD-012', 'Dell 27 Monitor SE2722H', '27-inch Full HD (1920x1080)\nIPS Panel\nHDMI & VGA Ports\n75Hz Refresh Rate\nAMD FreeSync', '3 Years', 45000.00),
('PRD-013', 'Logitech MK275 Wireless Keyboard & Mouse', 'Wireless 2.4GHz Connection\nFull-size Keyboard\nOptical Mouse\nLong Battery Life\nPlug & Play USB Receiver', '1 Year', 4500.00),
('PRD-014', 'Logitech MK345 Wireless Keyboard & Mouse', 'Wireless 2.4GHz Connection\nFull-size Keyboard with Palm Rest\nContoured Mouse\nLong Battery Life', '1 Year', 7500.00),
('PRD-015', 'Logitech M185 Wireless Mouse', 'Wireless 2.4GHz Connection\nOptical Tracking\n12-Month Battery Life\nPlug & Play USB Receiver', '1 Year', 1800.00),
('PRD-016', 'TP-Link Archer C6 AC1200 Router', 'Dual-Band Wi-Fi Router\n5GHz: 867Mbps, 2.4GHz: 300Mbps\n4 External Antennas\nGigabit Ethernet Ports', '3 Years', 8500.00),
('PRD-017', 'TP-Link Archer AX23 AX1800 Router', 'Wi-Fi 6 Dual-Band Router\n5GHz: 1201Mbps, 2.4GHz: 574Mbps\n4 External Antennas\nGigabit Ethernet Ports', '3 Years', 14500.00),
('PRD-018', 'D-Link DGS-1008A 8-Port Gigabit Switch', '8-Port Gigabit Ethernet Switch\n10/100/1000 Mbps\nPlug & Play\nEnergy Efficient', '3 Years', 5500.00),
('PRD-019', 'Western Digital 1TB External HDD', '1TB Storage Capacity\nUSB 3.0 Connection\nPortable Design\nBackup Software Included', '3 Years', 15000.00),
('PRD-020', 'Western Digital 2TB External HDD', '2TB Storage Capacity\nUSB 3.0 Connection\nPortable Design\nBackup Software Included', '3 Years', 22000.00),
('PRD-021', 'SanDisk 32GB USB Flash Drive', '32GB Storage Capacity\nUSB 3.0 Connection\nRead Speed up to 130MB/s\nCompact Design', '5 Years', 1200.00),
('PRD-022', 'SanDisk 64GB USB Flash Drive', '64GB Storage Capacity\nUSB 3.0 Connection\nRead Speed up to 130MB/s\nCompact Design', '5 Years', 1800.00),
('PRD-023', 'SanDisk 128GB USB Flash Drive', '128GB Storage Capacity\nUSB 3.0 Connection\nRead Speed up to 130MB/s\nCompact Design', '5 Years', 2800.00),
('PRD-024', 'APC Back-UPS 650VA BX650LI-MS', '650VA / 325W UPS\nLine Interactive\n4 Outlets\nAutomatic Voltage Regulation', '2 Years', 18500.00),
('PRD-025', 'APC Back-UPS 1100VA BX1100LI-MS', '1100VA / 550W UPS\nLine Interactive\n6 Outlets\nAutomatic Voltage Regulation', '2 Years', 28500.00),
('PRD-026', 'Microsoft Office 2021 Professional Plus', 'Word, Excel, PowerPoint\nOutlook, Access, Publisher\nOne-time Purchase\nLifetime License', 'Lifetime', 45000.00),
('PRD-027', 'Microsoft 365 Business Basic (Annual)', 'Web & Mobile Office Apps\n1TB OneDrive Storage\nMicrosoft Teams\nExchange Email', '1 Year', 15000.00),
('PRD-028', 'Norton 360 Standard (1 Device)', 'Antivirus & Malware Protection\nSecure VPN\nPassword Manager\nPC Cloud Backup', '1 Year', 4500.00),
('PRD-029', 'Kaspersky Total Security (3 Devices)', 'Antivirus & Malware Protection\nSafe Money\nPassword Manager\nFile Backup', '1 Year', 8500.00),
('PRD-030', 'HP 83A Original Toner Cartridge', 'Original HP Toner\n1,500 Pages Yield\nFor HP LaserJet Pro M201/M225 Series', '6 Months', 8500.00);

-- =====================================================
-- SAMPLE QUOTATION
-- =====================================================
INSERT INTO quotations (
    quotation_no, quotation_date, customer_ref_id, customer_id, company_name, 
    contact_person, phone, email, address, delivery_terms, validity,
    payment_terms, stock_availability, subtotal, discount, tax_amount, 
    grand_total, notes, prepared_by, status
) VALUES
(
    'QT-2025-0001', 
    '2025-01-15', 
    1, 
    'CUST-001',
    'Lanka Tiles PLC', 
    'Mr. Supun Nirman', 
    '114 526 700', 
    'supun@lankatiles.com', 
    'Lanka Tiles 215, Nawala Road, Colombo',
    'Within 2 Days after Confirmation',
    '100 Days',
    '30 Days Credit / COD',
    'Available',
    185000.00,
    0.00,
    33300.00,
    218300.00,
    'Price is negotiable.\nOn-site support included.',
    'M.ASHAN',
    'sent'
);

INSERT INTO quotation_items (
    quotation_id, product_ref_id, product_id, product_name, product_description, 
    warranty, quantity, unit_price, vat_amount, line_total, item_order
) VALUES
(
    1, 1, 'PRD-001',
    'DELL INSPIRON 3030 i3 14 GEN DESKTOP',
    'Intel Core i3 – 14100 14GEN Processor\n512GB M.2 PCIe NVMe SSD\n8GB DDR5 5600 MHz RAM',
    '3 Years On-Site',
    1, 185000.00, 33300.00, 218300.00, 1
);

-- =====================================================
-- VERIFY
-- =====================================================
SELECT '=== DATABASE CREATED SUCCESSFULLY ===' AS Message;

SELECT 'Customers' AS TableName, COUNT(*) AS Records FROM customers
UNION ALL
SELECT 'Products', COUNT(*) FROM products
UNION ALL
SELECT 'Quotations', COUNT(*) FROM quotations
UNION ALL
SELECT 'Quotation Items', COUNT(*) FROM quotation_items;

SET SQL_SAFE_UPDATES = 0;

-- Update existing records to have VAT enabled by default
UPDATE quotations SET vat_enabled = 1 WHERE id > 0 AND vat_enabled IS NULL;

SET SQL_SAFE_UPDATES = 1;

-- Verify
SELECT id, quotation_no, vat_enabled FROM quotations;