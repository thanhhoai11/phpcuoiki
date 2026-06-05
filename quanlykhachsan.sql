-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 05, 2026 lúc 01:13 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlykhachsan`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `amenity_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `amenities`
--

INSERT INTO `amenities` (`id`, `amenity_name`) VALUES
(1, 'WiFi miễn phí'),
(2, 'TV màn hình phẳng'),
(3, 'Điều hòa'),
(4, 'Máy sấy tóc'),
(5, 'Dịch vụ phòng'),
(6, 'Tủ lạnh'),
(7, 'Bồn tắm'),
(8, 'Máy chiếu'),
(9, 'Bữa sáng miễn phí');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adult_count` int(11) NOT NULL,
  `child_count` int(11) NOT NULL DEFAULT 0,
  `total_price` decimal(12,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `status` enum('pending','confirmed','checked_in','completed','cancelled') DEFAULT 'pending',
  `actual_check_in` datetime DEFAULT NULL,
  `actual_check_out` datetime DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `check_in`, `check_out`, `adult_count`, `child_count`, `total_price`, `payment_method`, `payment_status`, `status`, `actual_check_in`, `actual_check_out`, `booking_date`) VALUES
(1, 3, 'Nguyễn Văn A', 'customer@gmail.com', '0900000003', '2026-06-20', '2026-06-22', 2, 1, 1300000.00, 'vietqr', 'paid', 'confirmed', NULL, NULL, '2026-06-04 18:46:42'),
(2, 3, 'Nguyễn Văn A', 'customer@gmail.com', '0900000003', '2026-07-01', '2026-07-03', 1, 0, 800000.00, 'vietqr', 'paid', 'completed', NULL, NULL, '2026-06-04 18:46:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `booking_rooms`
--

CREATE TABLE `booking_rooms` (
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `booking_rooms`
--

INSERT INTO `booking_rooms` (`booking_id`, `room_id`) VALUES
(1, 16),
(2, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `price_policies`
--

CREATE TABLE `price_policies` (
  `id` int(11) NOT NULL,
  `policy_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `multiplier` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `price_policies`
--

INSERT INTO `price_policies` (`id`, `policy_name`, `start_date`, `end_date`, `multiplier`, `created_at`) VALUES
(1, 'Cuối tuần', '2026-01-01', '2026-12-31', 1.20, '2026-06-04 18:46:42'),
(2, 'Dịp lễ Tết', '2026-02-01', '2026-02-15', 1.50, '2026-06-04 18:46:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `room_type_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 2, 5, 'Phòng sạch sẽ, nhân viên thân thiện', '2026-06-04 18:46:42'),
(2, 3, 1, 4, 'Giá hợp lý, đầy đủ tiện nghi', '2026-06-04 18:46:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `status` enum('available','booked','occupied','cleaning','maintenance','overdue') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type_id`, `floor`, `status`) VALUES
(1, '101', 1, 1, 'available'),
(2, '102', 1, 1, 'available'),
(3, '103', 1, 1, 'available'),
(4, '104', 5, 1, 'available'),
(5, '105', 5, 1, 'available'),
(6, '201', 1, 2, 'available'),
(7, '202', 1, 2, 'available'),
(8, '203', 4, 2, 'available'),
(9, '204', 4, 2, 'available'),
(10, '205', 5, 2, 'available'),
(11, '301', 1, 3, 'available'),
(12, '302', 1, 3, 'available'),
(13, '303', 4, 3, 'available'),
(14, '304', 5, 3, 'available'),
(15, '305', 3, 3, 'available'),
(16, '401', 2, 4, 'available'),
(17, '402', 2, 4, 'available'),
(18, '403', 4, 4, 'available'),
(19, '404', 3, 4, 'available'),
(20, '405', 3, 4, 'available'),
(21, '501', 2, 5, 'available'),
(22, '502', 2, 5, 'available'),
(23, '503', 4, 5, 'available'),
(24, '504', 3, 5, 'available'),
(25, '505', 3, 5, 'available');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `max_adults` int(11) NOT NULL,
  `max_children` int(11) NOT NULL DEFAULT 0,
  `max_guests` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `room_types`
--

INSERT INTO `room_types` (`id`, `type_name`, `price`, `max_adults`, `max_children`, `max_guests`, `description`) VALUES
(1, 'Phòng Đơn Tiêu Chuẩn', 400000.00, 1, 0, 1, 'Phòng dành cho 1 khách'),
(2, 'Phòng Đôi Tiêu Chuẩn', 650000.00, 2, 1, 3, 'Phòng dành cho 2 người lớn và 1 trẻ em'),
(3, 'Phòng Triple', 900000.00, 3, 1, 4, 'Phòng dành cho nhóm khách'),
(4, 'Phòng Gia Đình', 1200000.00, 4, 2, 6, 'Phòng dành cho gia đình'),
(5, 'Phòng VIP', 2000000.00, 2, 2, 4, 'Phòng cao cấp với nhiều tiện nghi');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `room_type_amenities`
--

CREATE TABLE `room_type_amenities` (
  `room_type_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `room_type_amenities`
--

INSERT INTO `room_type_amenities` (`room_type_id`, `amenity_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(5, 6),
(5, 7),
(5, 8),
(5, 9);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('customer','receptionist','admin') NOT NULL DEFAULT 'customer',
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `phone`, `role`, `verified`, `otp_code`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Quản trị hệ thống', 'admin@hotel.com', '0900000001', 'admin', 1, NULL, '2026-06-04 18:46:41'),
(2, 'reception', 'reception123', 'Lễ tân khách sạn', 'reception@hotel.com', '0900000002', 'receptionist', 1, NULL, '2026-06-04 18:46:41'),
(3, 'customer', 'customer123', 'Nguyễn Văn A', 'customer@gmail.com', '0900000003', 'customer', 1, NULL, '2026-06-04 18:46:41');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `booking_rooms`
--
ALTER TABLE `booking_rooms`
  ADD PRIMARY KEY (`booking_id`,`room_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Chỉ mục cho bảng `price_policies`
--
ALTER TABLE `price_policies`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Chỉ mục cho bảng `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Chỉ mục cho bảng `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `room_type_amenities`
--
ALTER TABLE `room_type_amenities`
  ADD PRIMARY KEY (`room_type_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `price_policies`
--
ALTER TABLE `price_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `booking_rooms`
--
ALTER TABLE `booking_rooms`
  ADD CONSTRAINT `booking_rooms_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_rooms_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `room_type_amenities`
--
ALTER TABLE `room_type_amenities`
  ADD CONSTRAINT `room_type_amenities_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `room_type_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
