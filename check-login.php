<?php
session_start();
echo "<h1>Thông tin phiên làm việc</h1>";

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Kiểm tra biến session chính:</h2>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "Không có") . "<br>";
echo "tendangnhap: " . (isset($_SESSION['tendangnhap']) ? $_SESSION['tendangnhap'] : "Không có") . "<br>";
echo "vaitro: " . (isset($_SESSION['vaitro']) ? $_SESSION['vaitro'] : "Không có") . "<br>";
?>

<p>
    <a href="home.php">Quay lại trang chủ</a>
</p>