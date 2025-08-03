<?php
include 'db.php'; 

$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['id'] ?? null;

if ($orderId) {
    $stmt = $pdo->prepare("
        SELECT 
            donhang.iddonhang,
            donhang.idnguoidung,
            donhang.thoigiandat,
            donhang.sdtnguoinhan,
            donhang.diachi,
            nguoidung.hoten AS tennguoidhang,
            GROUP_CONCAT(CONCAT(sanpham.ten, ' (x', chitietdonhang.soluong, ')') ORDER BY chitietdonhang.idsanpham SEPARATOR ', ') AS sanpham,
            SUM(chitietdonhang.soluong * (sanpham.gia - (sanpham.gia * sanpham.giamgia / 100))) AS thanhtien
        FROM donhang
        JOIN nguoidung ON donhang.idnguoidung = nguoidung.idnguoidung
        JOIN chitietdonhang ON donhang.iddonhang = chitietdonhang.iddonhang
        JOIN sanpham ON chitietdonhang.idsanpham = sanpham.id
        WHERE donhang.iddonhang = :iddonhang
        GROUP BY donhang.iddonhang
    ");
    $stmt->execute(['iddonhang' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $order['thanhtien'] = number_format($order['thanhtien'], 0, ',', ',');
        echo json_encode(['success' => true, 'order' => $order]);  
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
}
?>
