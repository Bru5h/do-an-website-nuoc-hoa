<?php
session_start();
include 'db.php';

// Kiểm tra quyền admin
$tendangnhap = isset($_SESSION['tendangnhap']) ? $_SESSION['tendangnhap'] : null;
if (!$tendangnhap) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit();
}

// Kiểm tra vai trò của người dùng
$stmt = $pdo->prepare("SELECT vaitro FROM nguoidung WHERE tendangnhap = :tendangnhap");
$stmt->execute(['tendangnhap' => $tendangnhap]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['vaitro'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập trang này']);
    exit();
}

// Xử lý cập nhật trạng thái thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $magiaodich = isset($_POST['magiaodich']) ? $_POST['magiaodich'] : null;
    
    if (!$paymentId || !$status) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Cập nhật trạng thái thanh toán
        $stmt = $pdo->prepare("UPDATE thanhtoan SET trangthai = ?, magiaodich = ?, thoigian_thanhtoan = NOW() WHERE id_thanhtoan = ?");
        $stmt->execute([$status, $magiaodich, $paymentId]);
        
        // Nếu trạng thái là "Đã thanh toán", cập nhật thêm thông tin
        if ($status === 'Đã thanh toán') {
            // Lấy ID đơn hàng từ thanh toán
            $stmt = $pdo->prepare("SELECT iddonhang FROM thanhtoan WHERE id_thanhtoan = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($payment) {
                // Có thể thêm logic xử lý khi đơn hàng được thanh toán thành công
                // Ví dụ: cập nhật trạng thái đơn hàng, gửi email thông báo, v.v.
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thanh toán thành công']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
    exit();
}

// Hiển thị danh sách các giao dịch thanh toán
$stmt = $pdo->prepare("
    SELECT t.*, d.idnguoidung, d.thoigiandat, d.sdtnguoinhan, d.diachi, n.hoten
    FROM thanhtoan t
    JOIN donhang d ON t.iddonhang = d.iddonhang
    JOIN nguoidung n ON d.idnguoidung = n.idnguoidung
    ORDER BY t.created_at DESC
");
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thanh Toán</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="admin-donhang.css?<?php echo time(); ?>" />
    <style>
        .payment-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
        }
        .close-btn {
            float: right;
            cursor: pointer;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'admin-header.php'; ?>
    <div class="flex flex-col">
        <div class="flex flex-1">
            <?php include 'admin-sideitems.php'; ?>
            <main class="flex-1 p-6">
                <h1 class="text-3xl font-bold mb-6">Quản lý thanh toán</h1>
                
                <div class="bg-white shadow-md rounded-lg p-6">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">ID</th>
                                <th class="py-2 px-4 border-b">Đơn hàng</th>
                                <th class="py-2 px-4 border-b">Khách hàng</th>
                                <th class="py-2 px-4 border-b">Số tiền</th>
                                <th class="py-2 px-4 border-b">Phương thức</th>
                                <th class="py-2 px-4 border-b">Trạng thái</th>
                                <th class="py-2 px-4 border-b">Thời gian</th>
                                <th class="py-2 px-4 border-b">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="py-4 px-4 text-center">Không có giao dịch thanh toán nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b"><?= $payment['id_thanhtoan'] ?></td>
                                        <td class="py-2 px-4 border-b"><?= $payment['iddonhang'] ?></td>
                                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($payment['hoten']) ?></td>
                                        <td class="py-2 px-4 border-b"><?= number_format($payment['sotien'], 0, ',', '.') ?>đ</td>
                                        <td class="py-2 px-4 border-b"><?= $payment['phuongthuc'] ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <?php
                                            $statusClass = '';
                                            switch($payment['trangthai']) {
                                                case 'Đã thanh toán':
                                                    $statusClass = 'status-completed';
                                                    break;
                                                case 'Chưa thanh toán':
                                                    $statusClass = 'status-pending';
                                                    break;
                                                default:
                                                    $statusClass = 'status-failed';
                                            }
                                            ?>
                                            <span class="payment-status <?= $statusClass ?>"><?= $payment['trangthai'] ?></span>
                                        </td>
                                        <td class="py-2 px-4 border-b"><?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <button onclick="openUpdateModal(<?= $payment['id_thanhtoan'] ?>)" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-edit"></i> Cập nhật
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal cập nhật trạng thái thanh toán -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Cập nhật trạng thái thanh toán</h2>
            <form id="updatePaymentForm">
                <input type="hidden" id="paymentId" name="id">
                
                <div class="form-group">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status" required>
                        <option value="Chưa thanh toán">Chưa thanh toán</option>
                        <option value="Đã thanh toán">Đã thanh toán</option>
                        <option value="Thanh toán thất bại">Thanh toán thất bại</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="magiaodich">Mã giao dịch (nếu có):</label>
                    <input type="text" id="magiaodich" name="magiaodich">
                </div>
                
                <button type="submit" class="btn-submit">Cập nhật</button>
            </form>
        </div>
    </div>
    
    <script>
        // Mở modal cập nhật trạng thái
        function openUpdateModal(paymentId) {
            document.getElementById('paymentId').value = paymentId;
            document.getElementById('updateModal').style.display = 'block';
        }
        
        // Đóng modal
        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }
        
        // Xử lý cập nhật trạng thái thanh toán
        document.getElementById('updatePaymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('admin-don-hang-thanh-toan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal();
                    window.location.reload(); // Tải lại trang để cập nhật dữ liệu
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra khi cập nhật trạng thái thanh toán');
            });
        });
        
        // Đóng modal khi nhấp vào bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('updateModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 