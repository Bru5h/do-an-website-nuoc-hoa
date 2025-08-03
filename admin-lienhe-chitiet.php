<?php
include 'db.php';

if (isset($_GET['id'])) {
    $contactId = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT lienhe.*, nguoidung.hoten AS nguoidung_hoten, nguoidung.sdt AS nguoidung_sdt
        FROM lienhe
        LEFT JOIN nguoidung ON lienhe.idnguoidung = nguoidung.idnguoidung
        WHERE lienhe.idlienhe = :idlienhe
    ");
    $stmt->execute(['idlienhe' => $contactId]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contact) {
        $contact['trangThai'] = $contact['idnguoidung'] ? 'Đã tạo tài khoản' : 'Chưa tạo tài khoản';

        echo json_encode($contact);
    } else {
        echo json_encode(['error' => 'Không tìm thấy liên hệ.']);
    }
} else {
    echo json_encode(['error' => 'ID không hợp lệ.']);
}
?>
