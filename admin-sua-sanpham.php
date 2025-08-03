<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin từ POST
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

    // Truy vấn cơ sở dữ liệu để lấy hình ảnh cũ
    $stmt = $pdo->prepare("SELECT HinhAnh FROM sanpham WHERE Id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Nếu không có hình ảnh cũ, để giá trị mặc định là null
    $hinhAnh = isset($product['HinhAnh']) ? $product['HinhAnh'] : null;

    // Kiểm tra nếu có file hình ảnh mới
    if (isset($_FILES['hinhAnh']) && $_FILES['hinhAnh']['error'] == 0) {
        // Đường dẫn thư mục lưu ảnh
        $uploadDir = 'img/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Tạo thư mục nếu không có
        }

        $fileTmpPath = $_FILES['hinhAnh']['tmp_name'];
        $fileName = basename($_FILES['hinhAnh']['name']);
        $uploadFile = $uploadDir . $fileName;

        // Di chuyển file tải lên vào thư mục
        if (move_uploaded_file($fileTmpPath, $uploadFile)) {
            $hinhAnh = $uploadFile;  // Cập nhật đường dẫn hình ảnh mới
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi tải hình ảnh lên.']);
            exit;
        }
    }

    try {
        // Cập nhật thông tin sản phẩm vào cơ sở dữ liệu
        $stmt = $pdo->prepare("UPDATE sanpham 
                               SET Ten = :ten, PhanLoai = :phanLoai, NhaCungCap = :nhaCungCap, DungTich = :dungTich, 
                                   MoTa = :moTa, Gia = :gia, GiamGia = :giamGia, LyDoGiamGia = :lyDoGiamGia, SoLuongTonKho = :soLuongTonKho, 
                                   HinhAnh = :hinhAnh, NgayNhapHang = :ngayNhapHang
                               WHERE Id = :id");

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
            'hinhAnh' => $hinhAnh,  // Nếu không có ảnh mới, sẽ giữ lại ảnh cũ
            'ngayNhapHang' => $ngayNhapHang
        ]);

        // Trả về JSON thành công
        echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được sửa thành công!']);
        exit;  // Dừng ngay sau khi gửi phản hồi

    } catch (Exception $e) {
        // Trả về JSON lỗi nếu có vấn đề trong quá trình xử lý
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi sửa sản phẩm.']);
        exit;
    }
}
?>