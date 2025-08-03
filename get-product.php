<?php
include 'db.php';

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $stmt = $pdo->prepare("SELECT * FROM sanpham WHERE Id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'No product ID specified']);
}
?>
