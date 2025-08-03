<?php
session_start(); 
include 'db.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$queryDecoded = urldecode($query);

$items_per_page = 12;

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); 

$offset = ($current_page - 1) * $items_per_page;

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE PhanLoai  LIKE :query");
$total_stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
$total_stmt->execute();
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page); // Tính số trang

$stmt = $pdo->prepare("SELECT * FROM sanpham WHERE PhanLoai  LIKE :query LIMIT :limit OFFSET :offset");
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
    <title>Cửa Hàng Mỹ Phẩm</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="header.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="footer.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
    <tl class="font-semibold "><?= htmlspecialchars($queryDecoded) ?></tl>
        <section>            
            <div class="product-list">
                <?php if ($products): ?>
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
                                <button type="button" onclick="alert('Bạn cần đăng nhập để thêm vào giỏ!');" class="add-to-cart">Thêm vào giỏ</button>
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
    <?php include 'footer.php'; ?>
</body>
</html>
