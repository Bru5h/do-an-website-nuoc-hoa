<?php
session_start(); // Bắt đầu phiên làm việc

// Kết nối tới CSDL
$servername = "localhost";
$username = "root";
$password = "";
$database = "qldoanmypham";

$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$message = ""; // Khởi tạo biến thông điệp

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $tendangnhap = $_POST['tendangnhap'];
    $matkhau = $_POST['matkhau'];
    $sdt = $_POST['sdt'];
    $diachi = $_POST['diachi']; // Lấy địa chỉ

    // Kiểm tra hợp lệ số điện thoại
    if (!preg_match('/^[0-9]{10,11}$/', $sdt)) {
        $message = "Số điện thoại không hợp lệ!";
    } else {
        // Kiểm tra trùng tên đăng nhập hoặc email
        $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE tendangnhap = ? OR email = ?");
        $stmt->bind_param("ss", $tendangnhap, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Tên đăng nhập hoặc email đã tồn tại!";
        } else {
            // Thêm người dùng mới
            $stmt = $conn->prepare("INSERT INTO nguoidung (hoten, tendangnhap, matkhau, sdt, email, diachi) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $hoten, $tendangnhap, $matkhau, $sdt, $email, $diachi);

            if ($stmt->execute()) {
                // Lưu thông tin đăng nhập vào session
                $_SESSION['tendangnhap'] = $tendangnhap;
                $_SESSION['diachi'] = $diachi;
                header("Location: home.php");
                exit;
            } else {
                $message = "Đăng ký thất bại!";
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>
