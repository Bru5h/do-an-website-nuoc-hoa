<?php
session_start();
include 'db.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
$userId = $_SESSION['user_id'] ?? null; // Nếu không có user_id, giá trị mặc định là null

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

// Xử lý thêm sản phẩm vào giỏ hàng qua AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $productId = htmlspecialchars($_POST['product_id']);
    $productName = htmlspecialchars($_POST['product_name']);
    $productPrice = htmlspecialchars($_POST['product_price']);
    $productImg = htmlspecialchars($_POST['product_img']);
    $quantity = intval(htmlspecialchars($_POST['quantity']));

    if ($userId) {
        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $stmt = $pdo->prepare("SELECT * FROM giohang WHERE idnguoidung = ? AND Id = ?");
        $stmt->execute([$userId, $productId]);

        if ($stmt->rowCount() > 0) {
            // Cập nhật số lượng nếu đã có
            $stmt = $pdo->prepare("UPDATE giohang SET soLuong = soLuong + ? WHERE idnguoidung = ? AND Id = ?");
            $stmt->execute([$quantity, $userId, $productId]);
        } else {
            // Thêm sản phẩm mới vào giỏ hàng CSDL
            $stmt = $pdo->prepare("INSERT INTO giohang (idnguoidung, Id, soLuong, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $productId, $quantity]);
        }

        // Đếm số sản phẩm trong giỏ hàng
        $stmt = $pdo->prepare("SELECT SUM(soLuong) as total FROM giohang WHERE idnguoidung = ?");
        $stmt->execute([$userId]);
        $cartCount = $stmt->fetchColumn();

        // Trả về số lượng sản phẩm trong giỏ hàng
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
        exit();
    } else {
        // Nếu người dùng chưa đăng nhập
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
    <title>Cửa Hàng Nước Hoa</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header-da-dang-nhap.php'; ?>
    
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
                        <a href="chi-tiet-da-dang-nhap.php?Id=<?= $product['Id'] ?>" style="display: block; color: inherit; text-decoration: none;">
                            <img src="img/<?= $product['Id']?>.webp" alt="<?= htmlspecialchars($product['Ten']) ?>" />
                            <h3>
                                <a href="chi-tiet-da-dang-nhap.php?Id=<?= $product['Id'] ?>" style="display: block; color: inherit; text-decoration: none;">
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
                                <?php endif; ?>                            </div>

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
                    alert('Có lỗi xảy ra: ' + data.message);
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
