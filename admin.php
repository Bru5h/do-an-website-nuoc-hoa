<?php
session_start();
include 'db.php'; 

// Kiểm tra người dùng đã đăng nhập chưa
if (isset($_SESSION['tendangnhap'])) {
    $tendangnhap = $_SESSION['tendangnhap'];
    
    // Truy vấn thông tin người dùng
    $stmt = $pdo->prepare("SELECT hoten, sdt FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null;
}

// Truy vấn số lượng sản phẩm trong kho
$sql = "SELECT SUM(SoLuongTonKho) AS total_stock FROM sanpham";
$stmt = $pdo->prepare($sql); 
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_stock = $row['total_stock'] ?? 0; 

// Truy vấn số lượng người dùng
$sql = "SELECT COUNT(idnguoidung) AS total_users FROM nguoidung";
$stmt = $pdo->prepare($sql); 
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_users = $row['total_users'] ?? 0;

// Truy vấn tổng doanh thu
$selected_month = $_GET['month'] ?? date('Y-m');

$sql = "SELECT SUM(sanpham.Gia * chitietdonhang.soluong) AS total_revenue
        FROM chitietdonhang
        JOIN sanpham ON chitietdonhang.idsanpham = sanpham.Id
        JOIN donhang ON chitietdonhang.iddonhang = donhang.iddonhang
        WHERE DATE_FORMAT(donhang.thoigiandat, '%Y-%m') = :selected_month";
$stmt = $pdo->prepare($sql);
$stmt->execute(['selected_month' => $selected_month]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_revenue = $row['total_revenue'] ?? 0;

// Truy vấn số đơn hàng mới trong 24 giờ qua
$sql = "SELECT COUNT(iddonhang) AS total_orders_today
        FROM donhang
        WHERE thoigiandat > NOW() - INTERVAL 1 DAY";
$stmt = $pdo->prepare($sql); 
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_orders_today = $row['total_orders_today'] ?? 0;  

// Truy vấn tổng số đơn hàng 
$sql = "SELECT donhang.iddonhang, nguoidung.hoten, donhang.thoigiandat, 
               SUM(sanpham.Gia * chitietdonhang.soluong) AS total
        FROM donhang
        JOIN nguoidung ON donhang.idnguoidung = nguoidung.idnguoidung
        JOIN chitietdonhang ON donhang.iddonhang = chitietdonhang.iddonhang
        JOIN sanpham ON chitietdonhang.idsanpham = sanpham.Id
        GROUP BY donhang.iddonhang";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Truy vấn danh sách người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare("SELECT idnguoidung, hoten, sdt,email, vaitro FROM nguoidung");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Truy vấn danh sách liên hệ của người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare("SELECT * FROM lienhe");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Cửa Hàng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
    <link rel="stylesheet" type="text/css" href="admin.css?<?php echo time(); ?>" />
</head>
<body class="bg-gray-100">
<?php include 'admin-header.php'; ?>
<div class="flex flex-col">
        <div class="flex flex-1">
        <?php include 'admin-sideitems.php'; ?>
            <main class="flex-1 p-6">
                <div class="container mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="column bg-white p-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tổng sản phẩm trong kho</p>
                                    <p class="text-2xl font-semibold"><?= $total_stock?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column bg-white p-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-500 text-white mr-4">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tổng số người dùng</p>
                                    <p class="text-2xl font-semibold"><?= $total_users?></p>
                                </div>
                            </div>
                        </div>
                        <div class="column bg-white p-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-500 text-white mr-4">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tổng doanh thu</p>
                                    <p class="text-2xl font-semibold"><?= number_format($total_revenue, 0, ',', '.') ?>₫</p>
                                </div>
                            </div>
                             <form method="GET" class="mb-2">
    <label for="month" class="mr-2 text-gray-600">Chọn tháng:</label>
    <select name="month" id="month" onchange="this.form.submit()" class="border rounded px-2 py-1">
        <?php
        for ($i = 0; $i < 12; $i++) {
            $month = date('Y-m', strtotime("-$i months"));
            $selected = ($_GET['month'] ?? '') === $month ? 'selected' : '';
            echo "<option value='$month' $selected>$month</option>";
        }
        ?>
    </select>
</form>
                        </div>
                        <div class="column bg-white p-4 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-red-500 text-white mr-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <p class="text-gray-600">Đơn hàng mới</p>
                                    <p class="text-2xl font-semibold"><?= $total_orders_today?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Quản lý đơn hàng</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">ID đơn hàng</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Họ tên</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Ngày đặt</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center">#<?= htmlspecialchars($order['iddonhang']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($order['hoten']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= date('Y-m-d', strtotime($order['thoigiandat'])); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= number_format($order['total'], 0, ',', '.') ?>₫</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Quản lý người dùng</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">ID người dùng</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Họ tên</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Số điện thoại</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Email</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Vai trò</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center">#<?= htmlspecialchars($user['idnguoidung']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($user['hoten']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($user['sdt']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($user['email']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center">
                                                <?php 
                                                // Hiển thị vai trò nếu 0 = User và 1 = Admin
                                                echo ($user['vaitro'] == 1) ? 'Admin' : 'User';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Quản lý liên hệ</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">ID liên hệ</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Họ tên</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Số điện thoại</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Nội dung</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-center">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $contacts): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center">#<?= htmlspecialchars($contacts['idlienhe']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($contacts['hoten']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($contacts['sdt']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center"><?= htmlspecialchars($contacts['noidung']); ?></td>
                                            <td class="py-2 px-4 border-b border-gray-200 text-center">
                                                <?php 
                                                // Hiển thị vai trò nếu 0 = User và 1 = Admin
                                                echo ($contacts['idnguoidung'] == null) ? 'Chưa tạo tài khoản' : 'Đã tạo tài khoản';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>
</html>