<?php
include 'db.php';

// Kiểm tra nếu có dữ liệu gửi đến
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $id = $_POST['id'];
    $ten = $_POST['ten'];
    $phanLoai = $_POST['phanLoai'];
    $nhaCungCap = $_POST['nhaCungCap'];
    $dungTich = $_POST['dungTich'];
    $moTa = $_POST['moTa'];
    $gia = $_POST['gia'];
    $giamGia = $_POST['giamGia'];
    $lyDoGiamGia = isset($_POST['lyDoGiamGia']) ? $_POST['lyDoGiamGia'] : '';
    $soLuongTonKho = $_POST['soLuongTonKho'];
    $ngayNhapHang = $_POST['ngayNhapHang'];
    $daBan = 0; // Giá trị mặc định cho DaBan

    // Kiểm tra xem ID đã tồn tại trong cơ sở dữ liệu chưa
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE Id = :id");
    $checkStmt->execute(['id' => $id]);
    $idExists = $checkStmt->fetchColumn();

    if ($idExists > 0) {
        // Nếu ID đã tồn tại, trả về thông báo lỗi
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm này đã tồn tại. Vui lòng chọn ID khác.']);
        exit;
    }

    // Xử lý hình ảnh
    $hinhAnh = 'img/clone.webp'; // Hình ảnh mặc định nếu không có hình ảnh tải lên
    
    if (isset($_FILES['hinhAnh']) && $_FILES['hinhAnh']['error'] == 0) {
        $fileTmpPath = $_FILES['hinhAnh']['tmp_name'];
        $fileName = $_FILES['hinhAnh']['name'];
        $uploadDir = 'img/';
        
        // Kiểm tra nếu thư mục tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Nếu thư mục không tồn tại, tạo thư mục img
        }

        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $hinhAnh = $filePath;
        } else {
            // Nếu không upload được file, trả về thông báo lỗi
            echo json_encode(['success' => false, 'message' => 'Không thể upload hình ảnh: ' . $_FILES['hinhAnh']['error']]);
            exit;
        }
    }

    // Thêm dữ liệu vào cơ sở dữ liệu
    try {
        // Chuẩn bị câu lệnh SQL - bao gồm trường LyDoGiamGia
        $sql = "INSERT INTO sanpham (Id, Ten, PhanLoai, NhaCungCap, DungTich, MoTa, Gia, GiamGia, LyDoGiamGia, SoLuongTonKho, DaBan, HinhAnh, NgayNhapHang) 
                VALUES (:id, :ten, :phanLoai, :nhaCungCap, :dungTich, :moTa, :gia, :giamGia, :lyDoGiamGia, :soLuongTonKho, :daBan, :hinhAnh, :ngayNhapHang)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'ten' => $ten,
            'phanLoai' => $phanLoai,
            'nhaCungCap' => $nhaCungCap,
            'dungTich' => $dungTich,
            'moTa' => $moTa,
            'gia' => $gia,
            'giamGia' => $giamGia,
            'lyDoGiamGia' => $lyDoGiamGia,
            'soLuongTonKho' => $soLuongTonKho,
            'daBan' => $daBan,
            'hinhAnh' => $hinhAnh,
            'ngayNhapHang' => $ngayNhapHang
        ]);
        
        // Trả về JSON thành công nếu không có lỗi
        echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được thêm thành công!']);
    } catch (PDOException $e) {
        // Nếu có lỗi trong quá trình thêm dữ liệu
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm sản phẩm: ' . $e->getMessage()]);
    }
} else {
    // Trả về thông báo lỗi nếu không phải là POST request
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>