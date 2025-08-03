-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 09, 2025 lúc 05:37 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qldoanmypham`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `idchitiet` int(11) NOT NULL,
  `iddonhang` int(11) DEFAULT NULL,
  `idsanpham` varchar(10) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`idchitiet`, `iddonhang`, `idsanpham`, `soluong`) VALUES
(12, 7, '61', 16);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhgiasanpham`
--

CREATE TABLE `danhgiasanpham` (
  `iddanhgia` int(11) NOT NULL,
  `idnguoidung` int(6) NOT NULL,
  `idsanpham` varchar(10) NOT NULL,
  `noidung` text DEFAULT NULL,
  `diemdanhgia` int(1) NOT NULL,
  `thoigian` datetime NOT NULL DEFAULT current_timestamp(),
  `trangthai` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhgiasanpham`
--

INSERT INTO `danhgiasanpham` (`iddanhgia`, `idnguoidung`, `idsanpham`, `noidung`, `diemdanhgia`, `thoigian`, `trangthai`) VALUES
(3, 1, '61', 'tuyệt', 5, '2025-06-08 22:12:51', 1),
(4, 7, '64', 'gửi hàng hơi trễ', 2, '2025-06-08 22:13:13', 0),
(5, 7, '65', 'mùi hương được , nhưng có phần hơi nồng', 3, '2025-06-08 22:13:31', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `iddonhang` int(11) NOT NULL,
  `idnguoidung` int(6) DEFAULT NULL,
  `thoigiandat` datetime DEFAULT NULL,
  `sdtnguoinhan` varchar(15) DEFAULT NULL,
  `diachi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`iddonhang`, `idnguoidung`, `thoigiandat`, `sdtnguoinhan`, `diachi`) VALUES
(7, 1, '2025-06-08 22:04:35', '08867394444', 'vĩnh phúc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `idgiohang` int(11) NOT NULL,
  `idnguoidung` int(6) NOT NULL,
  `Id` varchar(10) NOT NULL,
  `soLuong` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lienhe`
--

CREATE TABLE `lienhe` (
  `idlienhe` int(11) NOT NULL,
  `idnguoidung` int(6) DEFAULT NULL,
  `hoten` varchar(100) DEFAULT NULL,
  `sdt` varchar(11) DEFAULT NULL,
  `noidung` text DEFAULT NULL,
  `ngaygui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `idnguoidung` int(6) NOT NULL,
  `tendangnhap` varchar(50) NOT NULL,
  `matkhau` varchar(50) NOT NULL,
  `hoten` varchar(50) NOT NULL,
  `sdt` varchar(11) NOT NULL,
  `vaitro` int(2) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`idnguoidung`, `tendangnhap`, `matkhau`, `hoten`, `sdt`, `vaitro`, `email`) VALUES
(1, 'a', '1', 'Nguyễn Duy Phúc', '08867394444', 0, 'khongcanbiet@gmail.com'),
(2, 'b', '1', 'Nguyễn Quyết Thắng', '123456789', 1, 'khongcanbiet@gmail.com'),
(7, 'c', '1', 'Nguyễn Thị Thu Hiền', '0974564664', 0, 'thuhientn0106@gmail.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `Id` varchar(10) NOT NULL,
  `Ten` varchar(100) NOT NULL,
  `PhanLoai` varchar(50) NOT NULL,
  `NhaCungCap` varchar(30) NOT NULL,
  `DungTich` int(4) NOT NULL,
  `MoTa` varchar(255) NOT NULL,
  `Gia` int(8) NOT NULL,
  `GiamGia` int(4) NOT NULL,
  `LyDoGiamGia` varchar(255) NOT NULL,
  `SoLuongTonKho` int(4) NOT NULL,
  `DaBan` int(5) NOT NULL,
  `HinhAnh` varchar(255) NOT NULL,
  `NgayNhapHang` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`Id`, `Ten`, `PhanLoai`, `NhaCungCap`, `DungTich`, `MoTa`, `Gia`, `GiamGia`, `LyDoGiamGia`, `SoLuongTonKho`, `DaBan`, `HinhAnh`, `NgayNhapHang`) VALUES
('60', 'Chanel-no-5', 'Chanel', 'Chanel', 200, 'Chanel N°5', 1499000, 10, 'khuyến mãi lớn', 23, 0, 'img/60.webp', '2025-05-30'),
('61', 'Chanel Coco Mademoiselle ', 'Chanel', 'Chanel', 250, '(Trẻ trung - Cam, hoa hồng, patchouli)', 1249000, 0, '', 37, 16, 'img/61.webp', '2025-04-17'),
('64', 'Miss Dior', 'Dior', 'Dior', 250, '(Lãng mạn - Hoa hồng, patchouli, vanilla)', 1799000, 15, 'mừng ngày quốc tế thiếu nhi', 20, 0, 'img/64.webp', '2025-03-20'),
('65', 'J’adore', 'Dior', 'Dior', 245, '(Quyến rũ - Hoa nhài, hoa lan, quả lê)', 1299000, 10, 'khuyến mãi lớn', 35, 0, 'img/65.webp', '2025-02-13'),
('85', 'Guilty Love Edition', 'Gucci', 'Gucci', 250, '(Ngọt ngào - Cam, hoa hồng, patchouli)', 1399000, 0, '', 50, 0, 'img/85.webp', '2025-05-28'),
('86', 'La Vie Est Belle', 'La Vie Est Belle', 'Lancôme', 245, '(Hạnh phúc - Iris, vani, patchouli)', 2499000, 30, 'khuyến mãi lớn', 69, 0, 'img/86.webp', '2025-06-19'),
('87', 'Black Orchid', 'Tom Ford', 'Tom Ford', 245, '(Bí ẩn - Truffle, hoa lan, gỗ đàn hương)', 1650000, 0, '', 45, 0, 'img/87.webp', '2025-04-16'),
('88', 'English Pear & Freesia', 'Jo Malone London', 'Jo Malone London', 250, '(Thanh lịch - Lê, hoa freesia, gỗ)', 1234000, 0, '', 23, 0, 'img/88.webp', '2025-06-01'),
('89', 'Daisy', 'Marc Jacobs', 'Marc Jacobs', 250, '(Tươi trẻ - Dâu tây, hoa nhài, vani)', 1190000, 10, 'khuyến mãi lớn', 33, 0, 'img/89.webp', '2025-04-26'),
('90', 'Flowerbomb', 'Viktor & Rolf ', 'Viktor & Rolf ', 250, '(Nữ tính - Hoa nhài, hoa hồng, vanilla)', 2456000, 0, '', 42, 0, 'img/90.webp', '2025-05-11');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `id_thanhtoan` int(11) NOT NULL,
  `iddonhang` int(11) NOT NULL,
  `sotien` decimal(15,2) NOT NULL,
  `trangthai` varchar(50) NOT NULL DEFAULT 'Chưa thanh toán',
  `phuongthuc` varchar(50) NOT NULL DEFAULT 'VietQR',
  `magiaodich` varchar(100) DEFAULT NULL,
  `thoigian_thanhtoan` datetime DEFAULT NULL,
  `noidung_thanhtoan` varchar(255) DEFAULT NULL,
  `qrcode_url` varchar(255) DEFAULT NULL,
  `stk_nguoinhan` varchar(50) NOT NULL DEFAULT '0000769380652',
  `ten_nguoinhan` varchar(100) NOT NULL DEFAULT 'NGUYEN QUYET THANG',
  `nganhang` varchar(50) NOT NULL DEFAULT 'MBBank',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`idchitiet`),
  ADD KEY `iddonhang` (`iddonhang`),
  ADD KEY `idsanpham` (`idsanpham`);

--
-- Chỉ mục cho bảng `danhgiasanpham`
--
ALTER TABLE `danhgiasanpham`
  ADD PRIMARY KEY (`iddanhgia`),
  ADD KEY `idnguoidung` (`idnguoidung`),
  ADD KEY `idsanpham` (`idsanpham`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`iddonhang`),
  ADD KEY `idnguoidung` (`idnguoidung`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`idgiohang`),
  ADD KEY `idnguoidung` (`idnguoidung`),
  ADD KEY `giohang_ibfk_2` (`Id`);

--
-- Chỉ mục cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  ADD PRIMARY KEY (`idlienhe`),
  ADD KEY `idnguoidung` (`idnguoidung`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`idnguoidung`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`Id`);

--
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`id_thanhtoan`),
  ADD KEY `fk_thanhtoan_donhang` (`iddonhang`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `idchitiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `danhgiasanpham`
--
ALTER TABLE `danhgiasanpham`
  MODIFY `iddanhgia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `iddonhang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `idgiohang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  MODIFY `idlienhe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `idnguoidung` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `id_thanhtoan` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`iddonhang`) REFERENCES `donhang` (`iddonhang`),
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`idsanpham`) REFERENCES `sanpham` (`Id`);

--
-- Các ràng buộc cho bảng `danhgiasanpham`
--
ALTER TABLE `danhgiasanpham`
  ADD CONSTRAINT `danhgiasanpham_ibfk_1` FOREIGN KEY (`idnguoidung`) REFERENCES `nguoidung` (`idnguoidung`) ON DELETE CASCADE,
  ADD CONSTRAINT `danhgiasanpham_ibfk_2` FOREIGN KEY (`idsanpham`) REFERENCES `sanpham` (`Id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`idnguoidung`) REFERENCES `nguoidung` (`idnguoidung`);

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`idnguoidung`) REFERENCES `nguoidung` (`idnguoidung`) ON DELETE CASCADE,
  ADD CONSTRAINT `giohang_ibfk_2` FOREIGN KEY (`Id`) REFERENCES `sanpham` (`Id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lienhe`
--
ALTER TABLE `lienhe`
  ADD CONSTRAINT `lienhe_ibfk_1` FOREIGN KEY (`idnguoidung`) REFERENCES `nguoidung` (`idnguoidung`);

--
-- Các ràng buộc cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `fk_thanhtoan_donhang` FOREIGN KEY (`iddonhang`) REFERENCES `donhang` (`iddonhang`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
