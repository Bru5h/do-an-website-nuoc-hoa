<?php
session_start();
include 'db.php';   

// Kiểm tra nếu người dùng đã đăng nhập
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo "Bạn chưa đăng nhập!";
    exit();
}

// Lấy thông tin khách hàng từ form (nếu có)
$customerName = '';
$customerPhone = '';
$customerAddress = '';
$paymentMethod = '';
$orderId = null;
$showQR = false;
$qrCodeUrl = '';
$totalPrice = 0;

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy thông tin từ form
    $customerName = $_POST['customer_name'] ?? '';
    $customerPhone = $_POST['customer_phone'] ?? '';
    $customerAddress = $_POST['customer_address'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $totalPrice = $_POST['total_price'] ?? 0;
    $cart = json_decode($_POST['cart'], true); // Giỏ hàng được mã hóa dưới dạng JSON

    // Kiểm tra nếu giỏ hàng rỗng
    if (empty($cart)) {
        echo "Giỏ hàng của bạn trống!";
        exit();
    }

    // Bắt đầu giao dịch (transaction) để đảm bảo tính toàn vẹn dữ liệu
    $pdo->beginTransaction();

    try {
        // 1. Lưu thông tin đơn hàng vào bảng donhang
        $stmt = $pdo->prepare("INSERT INTO donhang (idnguoidung, thoigiandat, sdtnguoinhan, diachi) 
                               VALUES (?, NOW(), ?, ?)");
        $stmt->execute([$userId, $customerPhone, $customerAddress]);
        
        // Lấy ID của đơn hàng vừa tạo
        $orderId = $pdo->lastInsertId();

        // 2. Lưu thông tin chi tiết đơn hàng vào bảng chitietdonhang
        foreach ($cart as $item) {
            // Trước khi lưu vào chi tiết đơn hàng, kiểm tra và trừ số lượng trong kho
            $productId = $item['product_id'];
            $productQuantity = $item['soLuong'];

            // Cập nhật số lượng sản phẩm trong kho
            $stmt = $pdo->prepare("SELECT SoLuongTonKho, DaBan FROM sanpham WHERE Id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $newStock = $product['SoLuongTonKho'] - $productQuantity; // Số lượng mới sau khi trừ
                $newSold = $product['DaBan'] + $productQuantity; // Số lượng đã bán

                // Kiểm tra xem số lượng tồn kho có đủ không
                if ($newStock < 0) {
                    throw new Exception("Sản phẩm {$item['product_name']} không đủ trong kho.");
                }

                // Cập nhật số lượng tồn kho và số lượng đã bán
                $stmt = $pdo->prepare("UPDATE sanpham SET SoLuongTonKho = ?, DaBan = ? WHERE Id = ?");
                $stmt->execute([$newStock, $newSold, $productId]);
            }

            // Lưu chi tiết đơn hàng vào bảng chitietdonhang
            $stmt = $pdo->prepare("INSERT INTO chitietdonhang (iddonhang, idsanpham, soluong) 
                                   VALUES (?, ?, ?)");
            $stmt->execute([$orderId, $productId, $productQuantity]);
        }

        // 3. Xử lý thanh toán nếu là VietQR
        if ($paymentMethod === 'VietQR') {
            // Tạo nội dung thanh toán
            $paymentContent = "THANHTOAN_DH" . $orderId;
            
            // Thêm vào bảng thanhtoan
            $stmt = $pdo->prepare("INSERT INTO thanhtoan (iddonhang, sotien, trangthai, phuongthuc, noidung_thanhtoan, stk_nguoinhan, ten_nguoinhan, nganhang) 
                                  VALUES (?, ?, 'Chưa thanh toán', 'VietQR', ?, '0000769380652', 'NGUYEN QUYET THANG', 'MBBank')");
            $stmt->execute([$orderId, $totalPrice, $paymentContent]);
            
            // Lấy ID thanh toán vừa tạo
            $paymentId = $pdo->lastInsertId();
            
            // Tạo URL QR code từ VietQR
            $bankId = 'MB'; // MBBank
            $accountNo = '0000769380652'; // Số tài khoản
            $accountName = 'NGUYEN QUYET THANG'; // Tên tài khoản
            $amount = $totalPrice; // Số tiền
            
            // Tạo URL QR code
            $qrCodeUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact.png?amount={$amount}&addInfo={$paymentContent}&accountName={$accountName}";
            
            // Cập nhật URL QR code vào bảng thanhtoan
            $stmt = $pdo->prepare("UPDATE thanhtoan SET qrcode_url = ? WHERE id_thanhtoan = ?");
            $stmt->execute([$qrCodeUrl, $paymentId]);
            
            // Hiển thị QR code cho người dùng
            $showQR = true;
        }

        // 4. Xóa tất cả sản phẩm trong giỏ hàng của người dùng sau khi đặt hàng
        $stmt = $pdo->prepare("DELETE FROM giohang WHERE idnguoidung = ?");
        $stmt->execute([$userId]);
        if (isset($_POST['save_address']) && $_POST['save_address'] == 1) {
    $stmt = $pdo->prepare("UPDATE nguoidung SET diachi = :diachi WHERE idnguoidung = :id");
    $stmt->execute([
        'diachi' => $_POST['customer_address'],
        'id' => $userId
    ]);
    // 5. Cập nhật địa chỉ mới làm mặc định nếu người dùng chọn
if (isset($_POST['save_address']) && $_POST['save_address'] == '1') {
    $stmt = $pdo->prepare("UPDATE nguoidung SET diachi = ? WHERE idnguoidung = ?");
    $stmt->execute([$customerAddress, $userId]);
}
}

        // Commit giao dịch nếu không có lỗi
        $pdo->commit();

    } catch (Exception $e) {
        // Nếu có lỗi xảy ra, rollback giao dịch để tránh dữ liệu bị hỏng
        $pdo->rollBack();
        echo "Có lỗi xảy ra: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đặt Hàng</title>
    
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="dat-hang.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="hoan-thanh-dat-hang.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .qr-code {
            width: 300px;
            height: 300px;
            margin: 20px 0;
        }
        .qr-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            max-width: 400px;
        }
        .qr-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }
        .qr-info-label {
            font-weight: bold;
            color: #555;
        }
        .qr-info-value {
            color: #333;
        }
        .payment-instructions {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .payment-instructions ul {
            margin-left: 20px;
        }
        .payment-instructions li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php include 'header-da-dang-nhap.php'; ?>

    <main>
        <div class="success-message">
            <h1><ql class="font-semibold">Đặt hàng thành công!</ql></h1>
            <p>Cảm ơn bạn,<ql class="font-semibold"> <?= htmlspecialchars($customerName) ?></ql>! Đơn hàng của bạn đã được đặt thành công.</p>
            <p>Chúng tôi sẽ liên hệ với bạn qua số điện thoại: <?= htmlspecialchars($customerPhone) ?>.</p>
            <p>Địa chỉ giao hàng: <?= htmlspecialchars($customerAddress) ?>.</p>
            
            <?php if ($showQR): ?>
            <div class="qr-container">
                <h2>Thanh toán qua VietQR</h2>
                <img src="<?= $qrCodeUrl ?>" alt="QR Code thanh toán" class="qr-code">
                
                <div class="qr-info">
                    <div class="qr-info-item">
                        <span class="qr-info-label">Ngân hàng:</span>
                        <span class="qr-info-value">MBBank</span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Số tài khoản:</span>
                        <span class="qr-info-value">0000769380652</span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Chủ tài khoản:</span>
                        <span class="qr-info-value">NGUYEN QUYET THANG</span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Số tiền:</span>
                        <span class="qr-info-value"><?= number_format($totalPrice, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Nội dung CK:</span>
                        <span class="qr-info-value">THANHTOAN_DH<?= $orderId ?></span>
                    </div>
                </div>
                
                <div class="payment-instructions">
                    <h3>Hướng dẫn thanh toán:</h3>
                    <ul>
                        <li>Mở ứng dụng ngân hàng hoặc ví điện tử của bạn</li>
                        <li>Quét mã QR hoặc chuyển khoản theo thông tin bên trên</li>
                        <li>Đơn hàng sẽ được xử lý sau khi chúng tôi nhận được thanh toán</li>
                        <li>Vui lòng không thay đổi nội dung chuyển khoản</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <a href='home-da-dang-nhap.php'><i class="fas fa-arrow-left align-center"></i> 
                <ql class="font-semibold">Quay lại trang chủ</ql> 
            </a>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
