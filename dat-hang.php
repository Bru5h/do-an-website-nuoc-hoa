<?php
session_start();
include 'db.php';   

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "Bạn chưa đăng nhập!";
    exit();
}

if ($userId) {
    $stm = $pdo->prepare("SELECT * FROM nguoidung WHERE idnguoidung = :userId");
$stm->execute(['userId' => $userId]);
$user = $stm->fetch(PDO::FETCH_ASSOC); // Có 'diachi' trong $user
} else {
    $user = null; 
}

// Lấy tất cả sản phẩm trong giỏ hàng của người dùng và tính giá sau khi giảm
$stmt = $pdo->prepare("
    SELECT gh.idgiohang, gh.soLuong, gh.created_at, 
           sp.Id as product_id, sp.Ten as product_name, sp.Gia as product_price, 
           sp.GiamGia as product_discount, sp.HinhAnh as product_img
    FROM giohang gh 
    JOIN sanpham sp ON gh.Id = sp.Id 
    WHERE gh.idnguoidung = ? 
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['remove_item_id'])) {
    $itemId = $_POST['remove_item_id'];
    $stmt = $pdo->prepare("DELETE FROM giohang WHERE idgiohang = ? AND idnguoidung = ?");
    $stmt->execute([$itemId, $userId]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Xử lý cập nhật số lượng sản phẩm
if (isset($_POST['quantity_action']) && isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];
    $currentQuantity = (int)$_POST['quantity'];

    if ($_POST['quantity_action'] === 'increase') {
        $newQuantity = $currentQuantity + 1;
    } else if ($_POST['quantity_action'] === 'decrease') {
        $newQuantity = max(1, $currentQuantity - 1);
    }

    $stmt = $pdo->prepare("UPDATE giohang SET soLuong = ? WHERE idgiohang = ? AND idnguoidung = ?");
    $stmt->execute([$newQuantity, $itemId, $userId]);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Xử lý cập nhật khi người dùng nhập vào textbox
if (isset($_POST['update_quantity']) && isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];
    $newQuantity = (int)$_POST['quantity'];

    if ($newQuantity < 1) {
        $newQuantity = 1; // Đảm bảo số lượng không nhỏ hơn 1
    }

    $stmt = $pdo->prepare("UPDATE giohang SET soLuong = ? WHERE idgiohang = ? AND idnguoidung = ?");
    $stmt->execute([$newQuantity, $itemId, $userId]);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Tính tổng trị giá giỏ hàng
$totalPrice = 0;
foreach ($cartItems as $item) {
    // Tính giá sau giảm (nếu có)
    $discountedPrice = $item['product_price'] * (1 - $item['product_discount'] / 100);
    $totalPrice += $discountedPrice * $item['soLuong'];
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng Của Bạn</title>
    
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="dat-hang.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <?php include 'header-da-dang-nhap.php'; ?>
    <main>
        <label1 class="font-semibold">Giỏ hàng của tôi</label1>
        <section>
            <?php if (empty($cartItems)): ?>
                <p class="no-orders">Chưa có mặt hàng nào trong giỏ.</p>
            <?php else: ?>
                <div class="order-details">
                    <?php foreach ($cartItems as $item): 
                        // Tính giá sau giảm cho mỗi sản phẩm
                        $discountedPrice = $item['product_price'] * (1 - $item['product_discount'] / 100);
                    ?>
                        <div class="product-item">
                            <img src="<?= $item['product_img'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-image">
                            <div class="product-info">
                                <p class="order-item-title"><?= htmlspecialchars($item['product_name']) ?></p>                  
                                
                                <div class="quantity">
                                        <form method="POST">
                                            <input type="hidden" name="item_id" value="<?= $item['idgiohang'] ?>">
                                            <button type="submit" class="quantity-btn" name="quantity_action" value="decrease" class="quantity-button">-</button>
                                            <input type="text" name="quantity" value="<?= $item['soLuong'] ?>" class="quantity-input" 
                                                onchange="this.form.submit()" />
                                            <button type="submit" class="quantity-btn" name="quantity_action" value="increase" class="quantity-button">+</button>
                                            <input type="hidden" name="update_quantity" value="1" />
                                        </form>           
                                </div>
                                
                                <p class="order-item-price"><?= number_format($discountedPrice * $item['soLuong'], 0, ',', '.') ?>đ</p>
                            </div>
                            <form method="POST" class="rem-btn">
                                <input type="hidden" name="remove_item_id" value="<?= $item['idgiohang'] ?>">
                                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');" class="remove-btn"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-details">
                    <div class="price">
                        <label2>Tổng tiền</label2>
                        <label2> <?= number_format($totalPrice, 0, ',', '.') ?>đ</label2>
                    </div>
                    <div class="ship ">
                        <label2>Vận chuyển</label2>
                        <label2>Miễn phí</label2>
                    </div>
                    <div class="total">
                        <label1 class="font-semibold">Tổng số tiền bạn cần thanh toán</label1>
                        <label1 class="font-semibold"> <?= number_format($totalPrice, 0, ',', '.') ?>đ</label1>
                    </div>
                </div>

                <div class="divider"></div>
                <label1>Thông tin giao hàng</label1>
                <form action="hoan-thanh-dat-hang.php" method="POST">
                    <div class="form-group">
                        <label for="customer-name"><rl>*</rl>Họ tên:</label>
                        <input class="customer-infor" type="text" id="customer-name" name="customer_name" value="<?= htmlspecialchars($user['hoten'])?>" required>
                    </div>

                    <div class="form-group">
                        <label for="customer-phone"><rl>*</rl>Số điện thoại:</label>
                        <input class="customer-infor" type="text" id="customer-phone" name="customer_phone" value="<?= $user['sdt']?>" required>
                    </div>

                   <div class="form-group">
    <label for="customer-address"><rl>*</rl>Địa chỉ:</label>
    <input class="customer-infor" type="text" id="customer-address" name="customer_address"
           value="<?= htmlspecialchars($user['diachi']) ?>" required>
</div>
<div class="form-group">
    <input type="checkbox" id="save-address" name="save_address" value="1">
    <label for="save-address">Lưu địa chỉ này làm mặc định cho tài khoản</label>
</div>

                    <div class="form-group payment-methods">
                        <label><rl>*</rl>Phương thức thanh toán:</label>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="payment-cod" name="payment_method" value="COD" checked>
                                <label for="payment-cod">Thanh toán khi nhận hàng (COD)</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="payment-vietqr" name="payment_method" value="VietQR">
                                <label for="payment-vietqr">Thanh toán qua VietQR</label>
                                <img src="https://vietqr.net/img/logo-vietqr.png" alt="VietQR" class="payment-icon">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="cart" value='<?= json_encode($cartItems) ?>'>
                    <input type="hidden" name="total_price" value="<?= $totalPrice ?>">
                    <button class="confirm" type="submit">Xác Nhận Đặt Hàng</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
