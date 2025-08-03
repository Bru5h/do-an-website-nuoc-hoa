<?php
session_start();
include 'db.php';

// Kiểm tra nếu người dùng đã đăng nhập
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit();
}

// Nhận ID đơn hàng từ request
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
    exit();
}

// Kiểm tra quyền truy cập đơn hàng
$stmt = $pdo->prepare("SELECT * FROM donhang WHERE iddonhang = ? AND idnguoidung = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập']);
    exit();
}

// Lấy thông tin thanh toán
$stmt = $pdo->prepare("SELECT * FROM thanhtoan WHERE iddonhang = ?");
$stmt->execute([$orderId]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin thanh toán']);
    exit();
}

// Trả về thông tin thanh toán
echo json_encode([
    'success' => true,
    'payment' => [
        'id' => $payment['id_thanhtoan'],
        'order_id' => $payment['iddonhang'],
        'amount' => $payment['sotien'],
        'status' => $payment['trangthai'],
        'method' => $payment['phuongthuc'],
        'qr_code' => $payment['qrcode_url'],
        'payment_content' => $payment['noidung_thanhtoan'],
        'created_at' => $payment['created_at']
    ]
]);
?> 