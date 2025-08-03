<?php
session_start(); // Bắt đầu phiên làm việc
include 'db.php';

// Xác định số mặt hàng mỗi trang
$items_per_page = 12;

// Xác định trang hiện tại từ query string
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Đảm bảo trang bắt đầu từ 1

// Tính toán OFFSET
$offset = ($current_page - 1) * $items_per_page;

// Đếm tổng số mặt hàng 
$total_stmt = $pdo->query("SELECT COUNT(*) FROM sanpham");
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page); // Tính số trang

// Lấy sản phẩm từ cơ sở dữ liệu với OFFSET và LIMIT
$stmt = $pdo->prepare("SELECT * FROM sanpham LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hiển thị thông báo lỗi đăng nhập
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
if ($login_error) {
    echo "<script>alert('$login_error');</script>";
    unset($_SESSION['login_error']);
}

// Hiển thị thông báo khôi phục mật khẩu
$reset_success = isset($_SESSION['reset_success']) ? $_SESSION['reset_success'] : '';
if ($reset_success) {
    echo "<script>alert('$reset_success');</script>";
    unset($_SESSION['reset_success']);
}

$reset_error = isset($_SESSION['reset_error']) ? $_SESSION['reset_error'] : '';
if ($reset_error) {
    echo "<script>alert('$reset_error');</script>";
    unset($_SESSION['reset_error']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Nước Hoa</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
    
    <section>
    <div class="banner">
        <img class="banner-image" src="./img/banner1.jpg" alt="Banner Image 1" />
        <img class="banner-image" src="./img/banner2.jpg" alt="Banner Image 2" />
        <img class="banner-image" src="./img/banner3.jpg" alt="Banner Image 3" />
        <button class="prev" onclick="showPreviousImage()">&#10094;</button>
        <button class="next" onclick="showNextImage()">&#10095;</button>
    </div>
    <script>
        let currentIndex = 0;
        const images = document.querySelectorAll('.banner-image');
        const totalImages = images.length;

        images[currentIndex].classList.add('active');

        function showNextImage() {
            images[currentIndex].classList.remove('active'); 
            currentIndex = (currentIndex + 1) % totalImages; 
            images[currentIndex].classList.add('active');
        }

        function showPreviousImage() {
            images[currentIndex].classList.remove('active'); 
            currentIndex = (currentIndex - 1 + totalImages) % totalImages; 
            images[currentIndex].classList.add('active');
        }
        setInterval(showNextImage, 3000); 
    </script>

    <div class="product-list">
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
                        <button type="button" onclick="alert('Bạn cần đăng nhập để thêm vào giỏ!');" class="add-to-cart">Thêm vào giỏ</button>
                    </a>
                </div>
        <?php endforeach;?>
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
    <?php include 'footer.php'; ?>
</body>
</html>
