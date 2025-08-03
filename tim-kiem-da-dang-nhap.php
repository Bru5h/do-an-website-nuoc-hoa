<?php
session_start(); // Bắt đầu phiên làm việc
include 'db.php';

// Lấy từ khóa tìm kiếm từ URL (tham số 'query')
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Xác định số mặt hàng mỗi trang
$items_per_page = 32;

// Xác định trang hiện tại từ query string
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Đảm bảo trang bắt đầu từ 1

// Tính toán OFFSET
$offset = ($current_page - 1) * $items_per_page;

// Đếm tổng số mặt hàng có tên sản phẩm khớp với từ khóa tìm kiếm
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE Ten LIKE :query");
$total_stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
$total_stmt->execute();
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page); // Tính số trang

// Lấy sản phẩm từ cơ sở dữ liệu với OFFSET và LIMIT dựa trên từ khóa tìm kiếm
$stmt = $pdo->prepare("SELECT * FROM sanpham WHERE Ten LIKE :query LIMIT :limit OFFSET :offset");
$stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
if ($login_error) {
    echo "<script>alert('$login_error');</script>";
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Cửa Hàng Mỹ Phẩm</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include 'header-da-dang-nhap.php'; ?>
    <main>
        <section>
            <h1>Kết quả tìm kiếm cho: <strong><?= htmlspecialchars($query) ?></strong></h1>

            <div class="product-list">
                <?php if ($products): ?>
                    <?php foreach ($products as $product): 
                        // Lấy giá gốc và tỷ lệ giảm giá
                        $originalPrice = $product['Gia'];
                        $discountPercentage = $product['GiamGia']; // Giảm giá theo phần trăm
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
                <?php else: ?>
                    <p>Không có sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?query=<?= urlencode($query) ?>&page=<?= $current_page - 1 ?>">« Trang trước</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?query=<?= urlencode($query) ?>&page=<?= $i ?>" <?= $i === $current_page ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?query=<?= urlencode($query) ?>&page=<?= $current_page + 1 ?>">Trang sau »</a>
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
            const quantity = 1; // Số lượng mặc định là 1

            // Gửi yêu cầu AJAX để thêm sản phẩm vào giỏ hàng
            fetch('home-da-dang-nhap.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'action': 'add_to_cart',
                    'product_id': productId,
                    'product_name': productName,
                    'product_price': productPrice,
                    'product_img': productImg,
                    'quantity': quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sản phẩm đã được thêm vào giỏ hàng! Bạn có ' + data.cart_count + ' sản phẩm trong giỏ hàng.');
                } else {
                    if (data.message === 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.') {
                        alert('Vui lòng đăng nhập trước khi thêm sản phẩm vào giỏ hàng.');
                        window.location.href = 'dang-nhap.php';
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
