<?php 
session_start();
include 'db.php'; // Kết nối đến cơ sở dữ liệu

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy user ID từ session
$userId = $_SESSION['user_id'] ?? null;

// Xử lý khi người dùng gửi thông tin liên hệ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = $_POST['name'];
    $sdt = $_POST['phone'];
    $noidung = $_POST['message'];
    $ngaygui = date('Y-m-d H:i:s'); // Lưu thời gian gửi

    // Lưu thông tin vào cơ sở dữ liệu
    $stmt = $pdo->prepare("INSERT INTO lienhe (idnguoidung, hoten, sdt, noidung, ngaygui) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $hoten, $sdt, $noidung, $ngaygui]); // Gửi $userId vào câu truy vấn

    echo "<script>alert('Thông tin liên hệ đã được gửi thành công!');</script>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Mỹ Phẩm</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="lien-he.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include 'header-da-dang-nhap.php'; ?>
<main>
    <h2>Liên Hệ</h2>
    <form id="contact-form" method="POST">
        <label for="name">Họ Tên <span style="color:red">*</span></label>
        <input class="contact" type="text" id="name" name="name" required>

        <label for="phone">Điện Thoại <span style="color:red">*</span></label>
        <input class="contact" type="tel" id="phone" name="phone" required>

        <label for="message">Nội Dung <span style="color:red">*</span></label>
        <textarea class="contact" id="message" name="message" required></textarea>

        <div class="button-container">
        <button class="send" type="reset"><i class="fas fa-undo"></i></button>
            <button class="send" type="submit">
            <i class="fas fa-envelope"></i></button>
        </div>
    </form>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
