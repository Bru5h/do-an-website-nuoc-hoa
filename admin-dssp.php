<?php

session_start();

include 'db.php';  

if (isset($_GET['Id'])) {
    $productId = $_GET['Id'];

    $stmt = $pdo->prepare("SELECT * FROM sanpham WHERE Id = :id");
    $stmt->execute(['id' => $productId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Sản phẩm không tồn tại']);
    }
} else {
    echo json_encode(['error' => 'Không có tham số Id']);
}
?>
