<?php
session_start();
include 'db.php';

// Kiểm tra người dùng đã đăng nhập chưa (kiểm tra cả hai biến session)
if (!isset($_SESSION['idnguoidung']) && !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vui lòng đăng nhập để đánh giá sản phẩm.";
    header("Location: chi-tiet.php?Id=" . $_POST['idsanpham']);
    exit();
}

// Kiểm tra xem form đã được gửi chưa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    // Sử dụng idnguoidung từ session nếu có, nếu không thì dùng user_id
    $idnguoidung = isset($_SESSION['idnguoidung']) ? $_SESSION['idnguoidung'] : $_SESSION['user_id'];
    $idsanpham = $_POST['idsanpham'];
    $noidung = $_POST['noidung'];
    $diemdanhgia = $_POST['diemdanhgia'];
    
    // Kiểm tra dữ liệu
    if (empty($idsanpham) || empty($diemdanhgia)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin.";
        header("Location: chi-tiet.php?Id=$idsanpham");
        exit();
    }
    
    try {
        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM danhgiasanpham WHERE idnguoidung = :idnguoidung AND idsanpham = :idsanpham");
        $stmt->execute([
            'idnguoidung' => $idnguoidung,
            'idsanpham' => $idsanpham
        ]);
        
        $exists = $stmt->fetchColumn();
        
        if ($exists > 0) {
            // Nếu đã đánh giá rồi thì cập nhật đánh giá cũ
            $stmt = $pdo->prepare("UPDATE danhgiasanpham SET noidung = :noidung, diemdanhgia = :diemdanhgia, thoigian = NOW() WHERE idnguoidung = :idnguoidung AND idsanpham = :idsanpham");
            $stmt->execute([
                'noidung' => $noidung,
                'diemdanhgia' => $diemdanhgia,
                'idnguoidung' => $idnguoidung,
                'idsanpham' => $idsanpham
            ]);
            
            $_SESSION['success'] = "Cập nhật đánh giá thành công!";
        } else {
            // Nếu chưa đánh giá thì thêm đánh giá mới
            $stmt = $pdo->prepare("INSERT INTO danhgiasanpham (idnguoidung, idsanpham, noidung, diemdanhgia) VALUES (:idnguoidung, :idsanpham, :noidung, :diemdanhgia)");
            $stmt->execute([
                'idnguoidung' => $idnguoidung,
                'idsanpham' => $idsanpham,
                'noidung' => $noidung,
                'diemdanhgia' => $diemdanhgia
            ]);
            
            $_SESSION['success'] = "Thêm đánh giá thành công!";
        }
        
        // Kiểm tra người dùng đang ở trang chi tiết thường hay trang đã đăng nhập
        if (isset($_SESSION['tendangnhap']) && $_SESSION['vaitro'] == 0) {
            header("Location: chi-tiet-da-dang-nhap.php?Id=$idsanpham");
        } else {
            header("Location: chi-tiet.php?Id=$idsanpham");
        }
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi: " . $e->getMessage();
        header("Location: chi-tiet.php?Id=$idsanpham");
        exit();
    }
}
?>