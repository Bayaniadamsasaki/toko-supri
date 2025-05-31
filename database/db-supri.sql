-- Database: `toko_sembako_supri`
DROP DATABASE IF EXISTS `toko_sembako_supri`;
CREATE DATABASE `toko_sembako_supri` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `toko_sembako_supri`;

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('cashier') NOT NULL DEFAULT 'cashier',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `users`
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier'); -- password: password

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `suppliers`
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `suppliers`
INSERT INTO `suppliers` (`name`, `contact_person`, `phone`, `address`) VALUES
('PT Indofood Sukses Makmur', 'John Doe', '021-12345678', 'Jakarta Pusat'),
('PT Unilever Indonesia', 'Jane Smith', '021-87654321', 'Jakarta Selatan'),
('PT Mayora Indah', 'Bob Johnson', '021-56781234', 'Tangerang');

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) UNIQUE,
  `category` varchar(100),
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `products`
INSERT INTO `products` (`name`, `code`, `category`, `price`, `stock`, `supplier_id`) VALUES
('Beras 5kg', 'BR001', 'Beras', 65000.00, 20, 1),
('Minyak Goreng 1L', 'MG001', 'Minyak', 25000.00, 30, 2),
('Gula Pasir 1kg', 'GP001', 'Gula', 15000.00, 25, 3),
('Tepung Terigu 1kg', 'TT001', 'Tepung', 12000.00, 15, 1),
('Mie Instan', 'MI001', 'Mie', 3000.00, 100, 1),
('Sabun Mandi', 'SM001', 'Sabun', 5000.00, 40, 2),
('Kecap Manis', 'KM001', 'Kecap', 10000.00, 20, 3),
('Telur 1kg', 'TL001', 'Telur', 28000.00, 15, NULL),
('Susu Kental Manis', 'SK001', 'Susu', 12000.00, 25, 2),
('Kopi Sachet', 'KS001', 'Kopi', 1500.00, 50, 3);

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `customers`
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `customers`
INSERT INTO `customers` (`name`, `phone`, `address`) VALUES
('Umum', NULL, NULL),
('Budi Santoso', '081234567890', 'Jl. Merdeka No. 10'),
('Siti Rahayu', '085678901234', 'Jl. Pahlawan No. 5'),
('Ahmad Hidayat', '089012345678', 'Jl. Sudirman No. 15');

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `sales`
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit') NOT NULL DEFAULT 'cash',
  `status` enum('completed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dummy untuk tabel sales
INSERT INTO `sales` (`date`, `customer_name`, `total`, `payment_method`, `status`) VALUES
('2024-03-01 08:00:00', 'Budi Santoso', 150000, 'cash', 'completed'),
('2024-03-01 09:30:00', 'Siti Rahayu', 250000, 'credit', 'completed'),
('2024-03-02 10:15:00', 'Ahmad Hidayat', 180000, 'cash', 'completed'),
('2024-03-02 14:45:00', 'Umum', 95000, 'cash', 'completed'),
('2024-03-03 11:20:00', 'Budi Santoso', 320000, 'credit', 'completed');

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `sale_details`
DROP TABLE IF EXISTS `sale_details`;
CREATE TABLE `sale_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dummy untuk tabel sale_details
INSERT INTO `sale_details` (`sale_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 2, 65000),    -- Beras 5kg x 2
(1, 2, 1, 25000),    -- Minyak Goreng 1L x 1
(2, 3, 5, 15000),    -- Gula Pasir 1kg x 5
(2, 4, 3, 12000),    -- Tepung Terigu 1kg x 3
(2, 5, 10, 3000),    -- Mie Instan x 10
(3, 6, 2, 5000),     -- Sabun Mandi x 2
(3, 7, 3, 10000),    -- Kecap Manis x 3
(3, 8, 2, 28000),    -- Telur 1kg x 2
(4, 9, 5, 12000),    -- Susu Kental Manis x 5
(4, 10, 10, 1500),   -- Kopi Sachet x 10
(5, 1, 3, 65000),    -- Beras 5kg x 3
(5, 2, 2, 25000),    -- Minyak Goreng 1L x 2
(5, 3, 4, 15000);    -- Gula Pasir 1kg x 4

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `purchases`
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit') NOT NULL DEFAULT 'cash',
  `status` enum('completed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `purchase_details`
DROP TABLE IF EXISTS `purchase_details`;
CREATE TABLE `purchase_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `receivables`
DROP TABLE IF EXISTS `receivables`;
CREATE TABLE `receivables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` datetime DEFAULT NULL,
  `status` enum('unpaid','paid','Sebagian') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `receivables_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `receivable_details`
DROP TABLE IF EXISTS `receivable_details`;
CREATE TABLE `receivable_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receivable_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `receivable_id` (`receivable_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `receivable_details_ibfk_1` FOREIGN KEY (`receivable_id`) REFERENCES `receivables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `receivable_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel untuk tabel `debts`
DROP TABLE IF EXISTS `debts`;
CREATE TABLE `debts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `debts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dummy untuk tabel purchases
INSERT INTO `purchases` (`date`, `supplier_id`, `total`, `payment_method`, `status`) VALUES
('2024-03-01 09:00:00', 1, 1500000, 'cash', 'completed'),
('2024-03-02 10:30:00', 2, 2500000, 'credit', 'completed'),
('2024-03-03 14:15:00', 1, 1800000, 'cash', 'completed'),
('2024-03-04 11:45:00', 3, 3200000, 'credit', 'completed'),
('2024-03-05 16:20:00', 2, 2100000, 'cash', 'completed');

-- Data dummy untuk tabel purchase_details
INSERT INTO `purchase_details` (`purchase_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 50, 15000),  -- Beras 50kg @15.000
(1, 2, 30, 20000),  -- Gula 30kg @20.000
(2, 3, 40, 25000),  -- Minyak Goreng 40 liter @25.000
(2, 4, 25, 30000),  -- Tepung Terigu 25kg @30.000
(3, 1, 60, 15000),  -- Beras 60kg @15.000
(3, 5, 35, 18000),  -- Telur 35kg @18.000
(4, 2, 45, 20000),  -- Gula 45kg @20.000
(4, 3, 50, 25000),  -- Minyak Goreng 50 liter @25.000
(5, 4, 30, 30000),  -- Tepung Terigu 30kg @30.000
(5, 5, 40, 18000);  -- Telur 40kg @18.000

-- Data dummy untuk tabel receivables
INSERT INTO `receivables` (`customer_id`, `amount`, `date`, `due_date`, `payment_date`, `status`, `notes`) VALUES
(1, 2500000, '2024-03-01 08:30:00', '2024-04-01', '2024-03-15 10:00:00', 'Sebagian', 'Pembayaran pertama'),
(2, 1800000, '2024-03-02 09:45:00', '2024-04-02', '2024-03-02 09:45:00', 'paid', 'Lunas'),
(3, 3200000, '2024-03-03 10:15:00', '2024-04-03', '2024-03-20 14:30:00', 'Sebagian', 'Pembayaran pertama'),
(1, 2100000, '2024-03-04 11:30:00', '2024-04-04', NULL, 'unpaid', 'Belum dibayar'),
(2, 2800000, '2024-03-05 14:20:00', '2024-04-05', '2024-03-05 14:20:00', 'paid', 'Lunas');

-- Data dummy untuk tabel receivable_details
INSERT INTO `receivable_details` (`receivable_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 100, 15000),  -- Beras 100kg @15.000
(1, 2, 50, 20000),   -- Gula 50kg @20.000
(2, 3, 60, 25000),   -- Minyak Goreng 60 liter @25.000
(2, 4, 40, 30000),   -- Tepung Terigu 40kg @30.000
(3, 1, 120, 15000),  -- Beras 120kg @15.000
(3, 5, 70, 18000),   -- Telur 70kg @18.000
(4, 2, 90, 20000),   -- Gula 90kg @20.000
(4, 3, 80, 25000),   -- Minyak Goreng 80 liter @25.000
(5, 4, 60, 30000),   -- Tepung Terigu 60kg @30.000
(5, 5, 80, 18000);   -- Telur 80kg @18.000

-- --------------------------------------------------------

-- Struktur tabel untuk Chart of Accounts (COA)
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('aset','kewajiban','modal','pendapatan','beban') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal akun dasar
INSERT INTO `accounts` (`code`, `name`, `type`) VALUES
('101', 'Kas', 'aset'),
('102', 'Piutang Usaha', 'aset'),
('201', 'Utang Usaha', 'kewajiban'),
('301', 'Modal', 'modal'),
('401', 'Penjualan', 'pendapatan'),
('501', 'Pembelian', 'beban');

-- --------------------------------------------------------

-- Struktur tabel untuk Jurnal Umum
CREATE TABLE IF NOT EXISTS `journals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Struktur tabel untuk Detail Jurnal
CREATE TABLE IF NOT EXISTS `journal_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `position` enum('debit','kredit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_id` (`journal_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `journal_details_ibfk_1` FOREIGN KEY (`journal_id`) REFERENCES `journals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_details_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;