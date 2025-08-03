<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $_SESSION['reset_error'] = "Vui lòng nhập email.";
        header("Location: home.php");
        exit();
    }

    try {
        // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
        $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['reset_error'] = "Email không tồn tại trong hệ thống.";
            header("Location: home.php");
            exit();
        }

        // Tạo mật khẩu mới ngẫu nhiên
        $newPassword = generateRandomPassword();

        // Cập nhật mật khẩu mới trong cơ sở dữ liệu
        $stmt = $pdo->prepare("UPDATE nguoidung SET matkhau = :matkhau WHERE email = :email");
        $stmt->execute([
            'matkhau' => $newPassword,
            'email' => $email
        ]);

        // Gửi email chứa mật khẩu mới
        if (sendPasswordResetEmail($email, $user['hoten'], $newPassword)) {
            $_SESSION['reset_success'] = "Mật khẩu mới đã được gửi đến email của bạn.";
        } else {
            $_SESSION['reset_error'] = "Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.";
        }

        header("Location: home.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['reset_error'] = "Có lỗi xảy ra: " . $e->getMessage();
        header("Location: home.php");
        exit();
    }
}

// Hàm tạo mật khẩu ngẫu nhiên
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $password .= $characters[$index];
    }
    
    return $password;
}

// Hàm gửi email khôi phục mật khẩu
function sendPasswordResetEmail($email, $name, $newPassword) {
    // Khởi tạo PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình server
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'thangzin95@gmail.com'; // Email người gửi
        $mail->Password = 'mbrxpjvkshafniak'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Cấu hình người gửi và người nhận
        $mail->setFrom('thangzin95@gmail.com', 'Cửa Hàng Nước Hoa');
        $mail->addAddress($email, $name);
        
        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Khôi phục mật khẩu - Cửa Hàng Nước Hoa';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #be005f;">Khôi phục mật khẩu</h2>
                <p>Xin chào ' . htmlspecialchars($name) . ',</p>
                <p>Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.</p>
                <p>Mật khẩu mới của bạn là: <strong>' . $newPassword . '</strong></p>
                <p>Vui lòng đăng nhập và thay đổi mật khẩu mới ngay khi có thể.</p>
                <p>Nếu bạn không thực hiện yêu cầu này, vui lòng liên hệ với chúng tôi ngay.</p>
                <p>Trân trọng,<br>Cửa Hàng Nước Hoa</p>
            </div>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Không thể gửi email: {$mail->ErrorInfo}");
        return false;
    }
}
?>