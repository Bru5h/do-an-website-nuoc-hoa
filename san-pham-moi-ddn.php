<?php
session_start();
include 'db.php';

$userId = $_SESSION['user_id'] ?? null;

$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); 

$offset = ($current_page - 1) * $items_per_page;

$total_stmt = $pdo->query("SELECT COUNT(*) FROM sanpham ");
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

$stmt = $pdo->prepare("SELECT * FROM sanpham WHERE NgayNhapHang >= NOW() - INTERVAL 1 MONTH LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImg = $_POST['product_img'];
    $quantity = $_POST['quantity'];

    if ($userId) {
        $stmt = $pdo->prepare("SELECT * FROM giohang WHERE idnguoidung = ? AND Id = ?");
        $stmt->execute([$userId, $productId]);

        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE giohang SET soLuong = soLuong + ? WHERE idnguoidung = ? AND Id = ?");
            $stmt->execute([$quantity, $userId, $productId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO giohang (idnguoidung, Id, soLuong, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $productId, $quantity]);
        }

        $stmt = $pdo->prepare("SELECT SUM(soLuong) AS total FROM giohang WHERE idnguoidung = ?");
        $stmt->execute([$userId]);
        $cartCount = $stmt->fetchColumn();

        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.']);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Nước Hoa - Sản phẩm mới</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <?php include 'header-da-dang-nhap.php'; ?>

    <main>
    <tl class="font-semibold ">Sản phẩm mới</tl>
        <section>
            <div class="product-list">
                <?php foreach ($products as $product): 
                    $originalPrice = $product['Gia'];
                    $discountPercentage = $product['GiamGia'];
                    $discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100); // Tính giá sau giảm
                    ?>
                    <div class="product-item">
                        <a href="chi-tiet.php?Id=<?= $product['Id'] ?>" style="display: block; color: inherit; text-decoration: none;">
                            <img src="<?= $product['HinhAnh']?>" alt="<?= htmlspecialchars($product['Ten']) ?>" />            
                            <h3>
                                <a href="chi-tiet.php?Id=<?= $product['Id'] ?>" style="display: block; color: inherit; text-decoration: none;">
                                    <?= htmlspecialchars($product['Ten']) ?>
                                </a>
                            </h3>
                            <span class="stock">Kho: <?= htmlspecialchars($product['SoLuongTonKho']) ?></span>

                            <div class="price-stock">
                                <?php if ($discountPercentage > 0): ?>
                                    <p class="discounted-price" style="color: #d9534f;">
                                        Giá: <?= number_format($discountedPrice, 0, ',', '.') ?>₫
                                    </p>
                                    <p class="original-price" style="text-decoration: line-through; color: #aaa;">
                                        <?= number_format($originalPrice, 0, ',', '.') ?>₫
                                    </p>
                                <?php else: ?>
                                    <p class="price">
                                        Giá: <?= number_format($originalPrice, 0, ',', '.') ?>₫
                                    </p>
                                <?php endif; ?>                        
                            </div>

                            <!-- Nút Thêm vào giỏ hàng sử dụng AJAX -->
                            <button class="add-to-cart" 
                                data-id="<?= $product['Id'] ?>" 
                                data-name="<?= htmlspecialchars($product['Ten']) ?>" 
                                data-price="<?= $product['Gia'] ?>" 
                                data-img="img/<?= $product['Id'] ?>.webp" 
                                onclick="addToCart(this)">Thêm vào giỏ
                            </button>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>">« Trang trước</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" <?= $i === $current_page ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>">Trang sau »</a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        function addToCart(button) {
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const productPrice = button.getAttribute('data-price');
            const productImg = button.getAttribute('data-img');
            const quantity = 1; 

            // Gửi yêu cầu AJAX để thêm sản phẩm vào giỏ hàng
            fetch('home-da-dang-nhap.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'add_to_cart',
                    product_id: productId,
                    product_name: productName,
                    product_price: productPrice,
                    product_img: productImg,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sản phẩm đã được thêm vào giỏ hàng! Bạn có ' + data.cart_count + ' sản phẩm trong giỏ hàng.');
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            });
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
