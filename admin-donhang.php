<?php
session_start();
include 'db.php'; 

// Lấy thông tin người dùng đăng nhập
if (isset($_SESSION['tendangnhap'])) {
    $tendangnhap = $_SESSION['tendangnhap'];

    $stmt = $pdo->prepare("SELECT hoten, sdt FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null;
}

// Lấy thông tin đơn hàng kết hợp với người đặt, chi tiết sản phẩm và tính thành tiền
$stmt = $pdo->prepare("
    SELECT 
        donhang.iddonhang,
        donhang.idnguoidung,
        donhang.thoigiandat,
        donhang.sdtnguoinhan,
        donhang.diachi,
        nguoidung.hoten AS tennguoidhang,
        GROUP_CONCAT(CONCAT(sanpham.ten, ' (x', chitietdonhang.soluong, ')') ORDER BY chitietdonhang.idsanpham SEPARATOR '<br>') AS sanpham,
        SUM(chitietdonhang.soluong * (sanpham.gia - (sanpham.gia * sanpham.giamgia / 100))) AS thanhtien
    FROM donhang
    JOIN nguoidung ON donhang.idnguoidung = nguoidung.idnguoidung
    JOIN chitietdonhang ON donhang.iddonhang = chitietdonhang.iddonhang
    JOIN sanpham ON chitietdonhang.idsanpham = sanpham.id
    GROUP BY donhang.iddonhang
    ORDER BY donhang.thoigiandat DESC;
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);  

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="bg-green-500 text-white p-4 rounded">Xóa đơn hàng thành công!</div>';
}

if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo '<div class="bg-red-500 text-white p-4 rounded">Lỗi: Không tìm thấy đơn hàng hoặc không thể xóa!</div>';
}

?>

<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Cửa Hàng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="admin-nguoidung.css?<?php echo time(); ?>" />  
    <link rel="stylesheet" type="text/css" href="admin-donhang.css?<?php echo time(); ?>" />  
</head>
<body class="bg-gray-100">   
<?php include 'admin-header.php'; ?>
<div class="min-h-screen  flex flex-col">
    <div class="flex flex-1">
        <?php include 'admin-sideitems.php'; ?>
        <main class="flex-1 p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Quản lý đơn hàng</h1>
        </div>
            
            <ul class="bg-white shadow-md rounded-lg border border-gray-200 order-list-container">
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <li id="order-<?php echo $order['iddonhang']; ?>" class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <div class="flex items-center">
                                <div>
                                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($order['tennguoidhang']); ?> (<?php echo htmlspecialchars($order['sdtnguoinhan']); ?>)</h3>
                                    <p class="text-gray-600">Địa chỉ: <?php echo htmlspecialchars($order['diachi']); ?></p>
                                    <p class="text-gray-600">Thời gian đặt: <?php echo htmlspecialchars($order['thoigiandat']); ?></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-gray-800">Sản phẩm:</p>
                                <p class="text-gray-800">
                                    <?php 
                                        $sanphamArray = explode('<br>', $order['sanpham']);
                                        $sanphamArray = array_slice($sanphamArray, 0, 2);
                                        $sanphamDisplay = implode('<br>', $sanphamArray);
                                        if (count($sanphamArray) < count(explode('<br>', $order['sanpham']))) {
                                            $sanphamDisplay .= ' ...'; 
                                        }
                                        echo $sanphamDisplay;
                                    ?>
                                </p>
                            </div>
                            <div >
                                <!-- Hiển thị Thành tiền -->
                                <p class="text-blue-600">Tổng tiền: </p>
                                <p class="text-blue-600"><?php echo number_format($order['thanhtien'], 0, ',', '.'); ?>đ</p>
                            </div>
                            <div>
                                <button class="text-blue-500 hover:text-blue-700" onclick="openOrderDetailModal(<?php echo $order['iddonhang']; ?>)">
                                    <i class="fas fa-info-circle"></i> Chi tiết
                                </button>
                                <button class="text-red-500 hover:text-red-700 ml-2" onclick="deleteOrder(<?php echo $order['iddonhang']; ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="p-4">
                        <p class="text-gray-600">Không có đơn hàng nào trong hệ thống.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </main>
    </div>
</div>
<!-- Modal Chi Tiết Đơn Hàng -->
<div id="orderDetailModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-3xl w-full">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Chi Tiết Đơn Hàng</h2>
            <button onclick="closeOrderModal()" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Form Đơn Hàng -->
        <form id="orderDetailForm" action="admin-donhang-chitiet.php" method="POST">
            <input type="hidden" name="iddonhang" id="orderId">

            <div class="prob">
                <label for="tennguoidhang" class="text-sm font-medium text-gray-700">Tên người nhận</label>
                <input type="text" name="tennguoidhang" id="tennguoidhang" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required readonly>
            </div>

            <div class="prob">
                <label for="sdtnguoinhan" class="text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="text" name="sdtnguoinhan" id="sdtnguoinhan" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required readonly>
            </div>

            <div class="prob">
                <label for="diachi" class="text-sm font-medium text-gray-700">Địa chỉ giao hàng</label>
                <input type="text" name="diachi" id="diachi" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required readonly>
            </div>

            <div class="prob">
                <label for="thoigiandat" class="text-sm font-medium text-gray-700">Thời gian đặt</label>
                <input type="text" name="thoigiandat" id="thoigiandat" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required readonly>
            </div>

            <div class="prob">
                <label for="sanpham" class="text-sm font-medium text-gray-700">Sản phẩm đã đặt</label>
                <textarea name="sanpham" id="sanpham" class="mt-1 p-2 w-full border border-gray-300 rounded-md" rows="4" required readonly></textarea>
            </div>
            
            <div class="prob">
                <label for="thanhtien" class="text-sm font-medium text-gray-700">Thành tiền</label>
                <input type="text" name="thanhtien" id="thanhtien" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required readonly>
            </div>
            <div class="prob mt-4 text-right">
                <button type="button" onclick="closeOrderModal()" class="bg-blue-500 text-white px-4 py-2 rounded-md">Đóng</button>            
            </div>
        </form>
    </div>
</div>


<script>
function openOrderDetailModal(orderId) {
    // Mở modal khi nhấn nút "Chi tiết"
    fetch('admin-donhang-chitiet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: orderId })  
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); 

        if (data.success) {
            // Cập nhật thông tin vào form modal
            document.getElementById('orderId').value = data.order.iddonhang;
            document.getElementById('tennguoidhang').value = data.order.tennguoidhang;
            document.getElementById('sdtnguoinhan').value = data.order.sdtnguoinhan;
            document.getElementById('diachi').value = data.order.diachi;
            document.getElementById('thoigiandat').value = data.order.thoigiandat;
            document.getElementById('sanpham').value = data.order.sanpham;
            document.getElementById('thanhtien').value = data.order.thanhtien;

            // Hiển thị modal
            document.getElementById('orderDetailModal').classList.remove('hidden');
            document.getElementById('orderDetailModal').style.display = 'flex';  // Chắc chắn hiển thị đúng
        } else {
            alert('Lỗi: Không thể tải chi tiết đơn hàng');
        }
    })
    .catch(error => {
        alert("Lỗi kết nối: " + error.message);  
    });
}

function closeOrderModal() {
    document.getElementById('orderDetailModal').classList.add('hidden');
    document.getElementById('orderDetailModal').style.display = 'none';  // Ẩn modal khi đóng
}


function deleteOrder(orderId) {
    if (confirm("Bạn có chắc chắn muốn xóa đơn hàng này không?")) {
        fetch('admin-donhang-xoa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: orderId }) 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderElement = document.getElementById('order-' + orderId);
                if (orderElement) {
                    orderElement.remove();  
                }
                alert("Đơn hàng đã được xóa thành công.");
            } else {
                alert("Lỗi: " + (data.error || "Không thể xóa đơn hàng."));
            }
        })
        .catch(error => {
            alert("Lỗi kết nối: " + error.message);  
        });
    }
}

</script>
</body>
</html>
