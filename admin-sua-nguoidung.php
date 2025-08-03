<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $id = $_POST['id'];
    $vaitro = $_POST['vaitro'];

    // Cập nhật vai trò trong cơ sở dữ liệu
    $stmt = $pdo->prepare("UPDATE nguoidung SET vaitro = :vaitro WHERE idnguoidung = :id");
    $stmt->execute(['vaitro' => $vaitro, 'id' => $id]);

    // Kiểm tra nếu có thay đổi
    if ($stmt->rowCount() > 0) {
        // Chuyển hướng về trang quản lý người dùng sau khi cập nhật
        header('Location: admin-nguoidung.php');
        exit;
    } else {
        echo "Có lỗi xảy ra khi cập nhật vai trò.";
    }
}
?>
