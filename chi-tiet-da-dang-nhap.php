<?php
session_start();
include 'db.php';

// Lấy ID sản phẩm từ URL
$Id = isset($_GET['Id']) ? $_GET['Id'] : null;

// Truy vấn để lấy thông tin sản phẩm
$stmt = $pdo->prepare("SELECT * FROM sanpham WHERE Id = :Id");
$stmt->execute(['Id' => $Id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: home.php");
    exit();
}

// Tính toán giá sau giảm (nếu có giảm giá)
$originalPrice = $product['Gia'];
$discountPercentage = $product['GiamGia']; // Giảm giá theo phần trăm
$discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100);

// Lấy danh sách đánh giá cho sản phẩm này
$stmt = $pdo->prepare("
    SELECT d.*, n.hoten 
    FROM danhgiasanpham d 
    JOIN nguoidung n ON d.idnguoidung = n.idnguoidung 
    WHERE d.idsanpham = :idsanpham AND d.trangthai = 1 
    ORDER BY d.thoigian DESC
");
$stmt->execute(['idsanpham' => $Id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính điểm đánh giá trung bình
$stmt = $pdo->prepare("
    SELECT AVG(diemdanhgia) as avg_rating, COUNT(*) as review_count 
    FROM danhgiasanpham 
    WHERE idsanpham = :idsanpham AND trangthai = 1
");
$stmt->execute(['idsanpham' => $Id]);
$rating_info = $stmt->fetch(PDO::FETCH_ASSOC);

$average_rating = $rating_info['avg_rating'] ? round($rating_info['avg_rating'], 1) : 0;
$review_count = $rating_info['review_count'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['Ten']) ?> - Cửa Hàng Mỹ Phẩm</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="chi-tiet.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header-da-dang-nhap.php'; ?>
    <main>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const decreaseButton = document.getElementById('decrease');
            const increaseButton = document.getElementById('increase');
            const quantityInput = document.getElementById('quantity');

            // Hàm giảm số lượng
            decreaseButton.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });

            // Hàm tăng số lượng
            increaseButton.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                quantityInput.value = currentValue + 1;
            });
        });

        function addToCart(button) {
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const productPrice = button.getAttribute('data-price');
            const productImg = button.getAttribute('data-img');
            const quantity = document.getElementById('quantity').value; // Lấy số lượng từ ô nhập

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
                    'quantity': quantity // Gửi số lượng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sản phẩm đã được thêm vào giỏ hàng! Bạn có ' + data.cart_count + ' sản phẩm trong giỏ hàng.');
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        </script>

        <div class="container">
            <div class="breadcrumb">
                <a href="home-da-dang-nhap.php">Trang chủ</a>
                /
                <a href="#"><?= htmlspecialchars($product['PhanLoai']) ?></a>
                /
                <?= htmlspecialchars($product['Ten']) ?>
            </div>
            <div class="product">
                <div class="product-image">
                    <img width="600" height="600" alt="<?= htmlspecialchars($product['Ten']) ?>" src="<?= $product['HinhAnh']?>" decoding="async" fetchpriority="high" sizes="(max-width:767px) 480px, 600px" draggable="false"/>
                </div>
                <div class="product-details">
                    <div class="product-title">
                        <?= htmlspecialchars($product['Ten']) ?>
                    </div>
                    <div class="brand">
                        <?= htmlspecialchars($product['NhaCungCap']) ?>
                    </div>
                    <div class="product-price">
                    <?php if ($discountPercentage > 0): ?>
                        <!-- Hiển thị giá gốc với dấu gạch ngang và giá sau giảm trên cùng một dòng -->
                        <p class="price">
                        <span class="discounted-price"><?= number_format($discountedPrice, 0, ',', '.') ?>₫</span>
                            <span class="original-price"><?= number_format($originalPrice, 0, ',', '.') ?>₫</span>
                        </p>
                    <?php else: ?>
                        <!-- Chỉ hiển thị giá gốc nếu không có giảm giá -->
                        <p class="price"><?= number_format($originalPrice, 0, ',', '.') ?>₫</p>
                    <?php endif; ?>
                </div>
                    
                    <div class="quantity">
                        <button class="special" id="decrease">-</button>
                        <input type="text" id="quantity" value="1" />
                        <button class="special" id="increase">+</button>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="button" class="add-to-cart" 
                                data-id="<?= $product['Id'] ?>" 
                                data-name="<?= htmlspecialchars($product['Ten']) ?>" 
                                data-price="<?= $product['Gia'] ?>" 
                                data-img="<?= $product['HinhAnh']?>"
                                onclick="addToCart(this)">Thêm vào giỏ hàng
                        </button>
                    <?php else: ?>
                        <button type="button" onclick="alert('Bạn cần đăng nhập để thêm vào giỏ!');" class="add-to-cart">Thêm vào giỏ hàng</button>
                    <?php endif; ?>
                    <div class="product-description">
                        <?= htmlspecialchars($product['MoTa']) ?>
                    </div>
                </div>
            </div>
            
            <!-- Phần đánh giá sản phẩm -->
            <div class="reviews-section">
                <h2>Đánh giá sản phẩm</h2>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-message alert-error">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-message alert-success">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Hiển thị đánh giá trung bình -->
                <div class="average-rating">
                    <span class="rating-number"><?= $average_rating ?></span>
                    <div class="stars-container">
                        <?php
                        // Hiển thị sao đánh giá trung bình
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $average_rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $average_rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="review-count">(<?= $review_count ?> đánh giá)</span>
                </div>
                
                <!-- Form đánh giá sản phẩm -->
                <?php if (isset($_SESSION['idnguoidung']) || isset($_SESSION['user_id'])): ?>
                    <div class="review-form">
                        <h3>Viết đánh giá của bạn</h3>
                        <form action="danh-gia.php" method="post">
                            <input type="hidden" name="idsanpham" value="<?= $Id ?>">
                            
                            <div class="rating-select">
                                <label for="rating">Đánh giá của bạn:</label>
                                <select name="diemdanhgia" id="rating" required>
                                    <option value="">-- Chọn số sao --</option>
                                    <option value="5">5 sao - Rất tốt</option>
                                    <option value="4">4 sao - Tốt</option>
                                    <option value="3">3 sao - Bình thường</option>
                                    <option value="2">2 sao - Không tốt</option>
                                    <option value="1">1 sao - Rất tệ</option>
                                </select>
                            </div>
                            
                            <textarea name="noidung" placeholder="Nhập nội dung đánh giá của bạn..." required></textarea>
                            
                            <button type="submit" class="submit-review">Gửi đánh giá</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Vui lòng <a href="#" id="loginBtn">đăng nhập</a> để đánh giá sản phẩm.</p>
                <?php endif; ?>
                
                <!-- Danh sách đánh giá -->
                <div class="review-list">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-user"><?= htmlspecialchars($review['hoten']) ?></span>
                                    <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['thoigian'])) ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    // Hiển thị số sao đánh giá
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['diemdanhgia']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="review-content"><?= nl2br(htmlspecialchars($review['noidung'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
