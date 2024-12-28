-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table webbanhang.carts: ~0 rows (approximately)
INSERT INTO `carts` (`id`, `user_id`, `created_at`) VALUES
	(1, 1, '2024-12-04 02:55:34');

-- Dumping data for table webbanhang.cart_items: ~1 rows (approximately)

-- Dumping data for table webbanhang.category: ~4 rows (approximately)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
	(1, 'Electronics', 'Devices, gadgets, and home appliances'),
	(2, 'Books', 'Printed and digital reading materials'),
	(3, 'Clothing', 'Apparel for men, women, and children'),
	(4, 'Furniture', 'Tables, chairs, and other home furnishings');

-- Dumping data for table webbanhang.orders: ~0 rows (approximately)
INSERT INTO `orders` (`id`, `name`, `phone`, `address`, `created_at`) VALUES
	(1, 'Phạm Xuân Quảng', '0862298983', 'Thị trấn trảng bom', '2024-11-26 13:27:02');

-- Dumping data for table webbanhang.order_details: ~2 rows (approximately)
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
	(1, 1, 2, 2, 1299.99),
	(2, 1, 3, 2, 159.99),
	(3, 1, 4, 1, 19.99);

-- Dumping data for table webbanhang.product: ~9 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`) VALUES
	(3, 'Bookshelf', 'Wooden bookshelf with 5 tiers', 159.99, '/public/images/test.png', 4),
	(4, 'T-shirt', 'Cotton T-shirt in various colors', 19.99, '/public/images/test.png', 3),
	(5, 'Novel', 'Bestselling mystery novel', 14.99, '/public/images/test.png', 2),
	(6, 'Refrigerator', 'Energy-efficient refrigerator with large capacity', 899.99, '/public/images/test.png', 1),
	(7, 'Dining Table', '6-seater dining table in oak finish', 349.99, '/public/images/test.png', 4),
	(12, 'Test api', 'Test api', 14.99, NULL, 1),
	(14, 'Test api post 1', 'Test api put', 14.99, NULL, 1),
	(15, 'Test api post 2', 'Test api put', 14.99, NULL, 1),
	(18, 'Sản phẩm 16', 'Test api put', 14.99, NULL, 1);

-- Dumping data for table webbanhang.roles: ~3 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
	(1, 'Admin', 'Quản trị viên hệ thống'),
	(2, 'Editor', 'Người chỉnh sửa nội dung'),
	(3, 'Viewer', 'Người xem nội dung');

-- Dumping data for table webbanhang.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
	(1, 'pxq', '$2y$12$YYLO3WjXAjU7exI/v6xhfOBnwpM1zFJ3I30CMjDacLI8LzVsZpj7i', '2024-12-04 01:35:06'),
	(2, 'user', '$2y$12$30VwQHGN73CiWDA0o6IPF./0uW46vIiVJ6bPIPJPCoJXkta6SzOcy', '2024-12-11 01:36:28');

-- Dumping data for table webbanhang.user_roles: ~2 rows (approximately)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
	(1, 1),
	(1, 2),
	(2, 3);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
