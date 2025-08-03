<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=qldoanmypham', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Kết nối thất bại: ' . $e->getMessage();
    exit;
}
?>
