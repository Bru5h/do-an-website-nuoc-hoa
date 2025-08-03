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

$stmt = $pdo->prepare("SELECT * FROM lienhe");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="bg-green-500 text-white p-4 rounded">Xóa liên hệ thành công!</div>';
}

if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo '<div class="bg-red-500 text-white p-4 rounded">Lỗi: Không tìm thấy liên hệ hoặc không thể xóa!</div>';
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
</head>
<body class="bg-gray-100">   
<?php include 'admin-header.php'; ?>
<div class="min-h-screen flex flex-col">
    <div class="flex flex-1">
        <?php include 'admin-sideitems.php'; ?>
        <main class="flex-1 p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Quản lý liên hệ</h1>
        </div> 
            <ul class="bg-white shadow-md rounded-lg border border-gray-200">
                <?php if ($contacts): ?>
                    <?php foreach ($contacts as $contact): ?>
                        <li id="contact-<?php echo $contact['idlienhe']; ?>" class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <div class="flex items-center">
                                <div>
                                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($contact['hoten']); ?> - <?php echo htmlspecialchars($contact['sdt']); ?></h3>
                                    <p class="text-gray-600">Nội dung: <?php echo htmlspecialchars(strlen($contact['noidung']) > 20 ? substr($contact['noidung'], 0, 20) . '...' : $contact['noidung']); ?></p>
                                </div>
                            </div>
                            <div>
                                <!-- Nút chi tiết -->
                                <button class="text-blue-500 hover:text-blue-700" onclick="openEditModal(<?php echo $contact['idlienhe']; ?>)">
                                    <i class="fas fa-info-circle"></i> Chi tiết
                                </button>

                                <!-- Nút xóa -->
                                <button class="text-red-500 hover:text-red-700 ml-2" onclick="deleteContact(<?php echo $contact['idlienhe']; ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600">Không có liên hệ nào trong hệ thống.</p>
                <?php endif; ?>
            </ul>
        </main>
    </div>
</div>

<!-- Modal thông tin liên hệ-->
<div id="editModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50">
    <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-150">
        <h2 class="text-xl font-semibold mb-4">Thông tin liên hệ</h2>
        
        <form id="editContactForm" action="admin-sua-lienhe.php" method="POST">
            <input type="hidden" name="id" id="contactId">

            <div class="prob">
                <label for="hoten" class="block text-sm font-medium text-gray-700">Tên người dùng</label>
                <input type="text" name="hoten" id="hoten" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>
            
            <div class="prob">
                <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="text" name="sdt" id="sdt" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>

            <div class="prob">
                <label for="trangThai" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <input type="text" name="trangThai" id="trangThai" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>

            <div class="prob">
                <label for="noiDung" class="text-sm font-medium text-gray-700">Nội dung</label>
                <textarea name="noiDung" id="noiDung" class="mt-1 p-2 w-full border border-gray-300 rounded-md" required=""></textarea>
            </div>
            
            <div class="mt-4 text-right">
                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md" onclick="closeEditModal()">Đóng</button>    
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(contactId) {
        const modal = document.getElementById('editModal');
        const contactIdInput = document.getElementById('contactId');
        const hotenInput = document.getElementById('hoten');
        const sdtInput = document.getElementById('sdt');
        const noiDungInput = document.getElementById('noiDung');
        const trangThaiInput = document.getElementById('trangThai');

        fetch(`admin-lienhe-chitiet.php?id=${contactId}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    hotenInput.value = data.hoten;
                    sdtInput.value = data.sdt;
                    noiDungInput.value = data.noidung;
                    trangThaiInput.value = data.idnguoidung ? 'Đã tạo tài khoản' : 'Chưa tạo tài khoản';
                    contactIdInput.value = data.idlienhe;
                }
            });

        modal.classList.remove('hidden'); 
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden'); 
    }

    function deleteContact(contactId) {
        if (confirm("Bạn có chắc chắn muốn xóa liên hệ này không?")) {
            fetch('admin-lienhe-xoa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: contactId }) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contactElement = document.getElementById('contact-' + contactId);
                    if (contactElement) {
                        contactElement.remove();
                    }
                    alert("Liên hệ đã được xóa thành công.");
                } else {
                    alert("Lỗi: " + (data.error || "Không thể xóa liên hệ."));
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
