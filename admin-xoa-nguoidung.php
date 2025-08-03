<?php
// admin-xoa-nguoidung.php
include 'db.php';

// Kiểm tra xem có nhận được dữ liệu POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        $id = $data['id'];

        // Chuẩn bị và thực hiện xóa người dùng khỏi cơ sở dữ liệu
        $stmt = $pdo->prepare("DELETE FROM nguoidung WHERE idnguoidung = :id");
        $stmt->execute(['id' => $id]);

        // Kiểm tra xem có xóa thành công không
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa người dùng.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    }
}
?>
