<?php
session_start();
include 'db.php';

// Kiểm tra đăng nhập và phân quyền
if (!isset($_SESSION['idnguoidung']) || $_SESSION['vaitro'] != 1) {
    header('Location: home.php');
    exit();
}

// Xử lý thay đổi trạng thái đánh giá
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Lấy trạng thái hiện tại
    $stmt = $pdo->prepare("SELECT trangthai FROM danhgiasanpham WHERE iddanhgia = :id");
    $stmt->execute(['id' => $id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($review) {
        // Đảo ngược trạng thái
        $new_status = $review['trangthai'] == 1 ? 0 : 1;
        
        // Cập nhật trạng thái mới
        $stmt = $pdo->prepare("UPDATE danhgiasanpham SET trangthai = :trangthai WHERE iddanhgia = :id");
        $stmt->execute([
            'trangthai' => $new_status,
            'id' => $id
        ]);
        
        $_SESSION['success'] = "Đã cập nhật trạng thái đánh giá!";
        header('Location: quan-ly-danh-gia.php');
        exit();
    }
}

// Xử lý xóa đánh giá
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Xóa đánh giá
    $stmt = $pdo->prepare("DELETE FROM danhgiasanpham WHERE iddanhgia = :id");
    $stmt->execute(['id' => $id]);
    
    $_SESSION['success'] = "Đã xóa đánh giá thành công!";
    header('Location: quan-ly-danh-gia.php');
    exit();
}

// Phân trang
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Lấy tổng số đánh giá
$stmt = $pdo->query("SELECT COUNT(*) FROM danhgiasanpham");
$total_reviews = $stmt->fetchColumn();
$total_pages = ceil($total_reviews / $items_per_page);

// Lấy danh sách đánh giá với phân trang
$stmt = $pdo->prepare("
    SELECT d.*, n.hoten, s.Ten as tensanpham 
    FROM danhgiasanpham d 
    JOIN nguoidung n ON d.idnguoidung = n.idnguoidung 
    JOIN sanpham s ON d.idsanpham = s.Id 
    ORDER BY d.thoigian DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - Cửa Hàng Mỹ Phẩm</title>
    <link rel="stylesheet" type="text/css" href="style.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="admin.css?<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .review-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .review-table th, .review-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .review-table th {
            background-color: #f9f9f9;
        }
        
        .status-active, .status-inactive {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .status-active {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .status-inactive {
            background-color: #f2dede;
            color: #a94442;
        }
        
        .action-btn {
            margin-right: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
        }
        
        .edit-btn {
            background-color: #5bc0de;
        }
        
        .delete-btn {
            background-color: #d9534f;
        }
        
        .toggle-btn {
            background-color: #f0ad4e;
        }
        
        .review-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #337ab7;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .pagination {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="admin.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại trang quản trị</a>
        
        <div class="admin-header">
            <h1>Quản lý đánh giá sản phẩm</h1>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-message alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-message alert-error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <table class="review-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Người đánh giá</th>
                    <th>Điểm đánh giá</th>
                    <th>Nội dung</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= $review['iddanhgia'] ?></td>
                        <td>
                            <a href="chi-tiet.php?Id=<?= $review['idsanpham'] ?>" target="_blank">
                                <?= htmlspecialchars($review['tensanpham']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($review['hoten']) ?></td>
                        <td>
                            <?php
                            // Hiển thị sao đánh giá
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review['diemdanhgia']) {
                                    echo '<i class="fas fa-star" style="color: #f39c12;"></i>';
                                } else {
                                    echo '<i class="far fa-star" style="color: #f39c12;"></i>';
                                }
                            }
                            ?>
                        </td>
                        <td class="review-content" title="<?= htmlspecialchars($review['noidung']) ?>">
                            <?= htmlspecialchars(substr($review['noidung'], 0, 50)) ?>
                            <?= (strlen($review['noidung']) > 50) ? '...' : '' ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($review['thoigian'])) ?></td>
                        <td>
                            <?php if ($review['trangthai'] == 1): ?>
                                <span class="status-active">Hiển thị</span>
                            <?php else: ?>
                                <span class="status-inactive">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="quan-ly-danh-gia.php?action=toggle&id=<?= $review['iddanhgia'] ?>" 
                               class="action-btn toggle-btn" 
                               onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái đánh giá này?')">
                                <?= $review['trangthai'] == 1 ? 'Ẩn' : 'Hiển thị' ?>
                            </a>
                            <a href="quan-ly-danh-gia.php?action=delete&id=<?= $review['iddanhgia'] ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (count($reviews) == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Không có đánh giá nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Phân trang -->
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
    </div>
</body>
</html> 