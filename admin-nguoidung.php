<?php
session_start();
include 'db.php'; 

if (isset($_SESSION['tendangnhap'])) {
    $tendangnhap = $_SESSION['tendangnhap'];

    $stmt = $pdo->prepare("SELECT hoten, sdt FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null;
}

$stmt = $pdo->prepare("SELECT * FROM nguoidung");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<div class="flex flex-col">
    <div class="flex flex-1">
        <?php include 'admin-sideitems.php'; ?>
        <main class="flex-1 p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Quản lý người dùng</h1>
        </div>
            
            <ul class="bg-white shadow-md rounded-lg border border-gray-200">
                <?php if ($users): ?>
                    <?php foreach ($users as $users): ?>
                        <li id="user-<?php echo $users['idnguoidung']; ?>" class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <div class="flex items-center">
                                <div>
                                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($users['hoten']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($users['sdt']); ?></p>
                                </div>
                            </div>
                            <div>
                                <!-- Nút chi tiết -->
                                <button class="text-blue-500 hover:text-blue-700" onclick="openEditModal(<?php echo $users['idnguoidung']; ?>)">
                                    <i class="fas fa-info-circle"></i> Chi tiết
                                </button>

                                <!-- Nút xóa -->
                                <button class="text-red-500 hover:text-red-700 ml-2" onclick="deleteUser(<?php echo $users['idnguoidung']; ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="p-4">
                        <p class="text-gray-600">Không có người dùng nào trong hệ thống.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </main>
    </div>
</div>

<!-- Modal thông tin người dùng-->
<div id="editModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-50">
    <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-150">
        <h2 class="text-xl font-semibold mb-4" id="modalTitle">Thông tin người dùng</h2>
        
        <form id="userForm" action="admin-sua-nguoidung.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="userId">

            <div class="prob">
                <label for="hoten" class="block text-sm font-medium text-gray-700">Tên người dùng</label>
                <input type="text" name="hoten" id="hoten" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>
            
            <div class="prob">
                <label for="sdt" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                <input type="text" name="sdt" id="sdt" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>

            <div class="prob">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="mt-1 p-2 w-full border border-gray-300 rounded-md" readonly>
            </div>

            <div class="prob">
                <label for="vaitro" class="block text-sm font-medium text-gray-700">Vai trò</label>
                <select name="vaitro" id="vaitro" class="mt-1 p-2 w-full border border-gray-300 rounded-md">
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                </select>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md" id="submitBtn">Cập nhật</button>
                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md" onclick="closeEditModal()">Đóng</button>    
            </div>
        </form>
    </div>
</div>

<script>
// Mở modal và tải thông tin người dùng
function openEditModal(userId) {
    fetch('admin-thongtinnguoidung.php?Id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Không tìm thấy người dùng!');
                return;
            }

            // Điền thông tin vào form
            document.getElementById('userId').value = data.idnguoidung;
            document.getElementById('hoten').value = data.hoten;
            document.getElementById('sdt').value = data.sdt;
            document.getElementById('email').value = data.email;
            document.getElementById('vaitro').value = data.vaitro; 

            // Đổi tiêu đề modal khi chỉnh sửa
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa thông tin người dùng';
            document.getElementById('submitBtn').textContent = 'Cập nhật'; 
            
            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error fetching user details:', error);
            alert('Lỗi khi tải thông tin người dùng.');
        });
}

// Đóng modal
function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('hidden');
}

// Đóng modal khi nhấp ngoài vùng modal
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) { 
        closeEditModal();
    }
});

function deleteUser(userId) {
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này không?')) {
        
        fetch('admin-xoa-nguoidung.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: userId }) 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const userElement = document.getElementById('user-' + userId);
                if (userElement) {
                    userElement.remove(); 
                }
                alert('Người dùng đã được xóa thành công!');
            } else {
                alert('Có lỗi xảy ra khi xóa người dùng: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            alert('Không thể xóa người dùng. Vui lòng thử lại sau.');
        });
    }
}
</script>

</body>
</html>
