<?php
include 'db.php';

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE idnguoidung = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid ID']);
}
?>
