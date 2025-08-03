<?php
// Kiểm tra xem đã có session tendangnhap hay chưa
if (isset($_SESSION['tendangnhap'])) {
    $tendangnhap = $_SESSION['tendangnhap'];
    
    // Lấy thông tin người dùng từ cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT hoten, sdt FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Nếu chưa đăng nhập, thiết lập user là mảng rỗng
    $user = [];
}
?>
<nav class="bg-white border-gray-200 dark:bg-gray-900">
    <div class="flex items-center justify-between mx-auto p-4 ml-2 mr-2">
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Quản Lý Cửa Hàng</span>

        <div class="relative">
            <button type="button" class="avatar text-sm rounded-full" id="user-menu-button" aria-expanded="false"
                onclick="toggleDropdown(event)">
                <span class="sr-only">Open user menu</span>
                <i class="fas fa-user-circle" style="font-size:28px"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="user-dropdown"
                class="dropdown-menu absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600 z-50 hidden">
                <div class="user-dropdown px-4 py-3">
                    <span
                        class="block text-sm text-gray-900 dark:text-white"><?= isset($user['hoten']) ? $user['hoten'] : 'Admin' ?></span>
                    <span
                        class="block text-sm text-gray-500 truncate dark:text-gray-400"><?= isset($user['sdt']) ? $user['sdt'] : '' ?></span>
                </div>
                <ul class="log-out">
                    <a href="home.php" class="dropdown-item">Đăng xuất</a>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
// Hàm toggle dropdown khi nhấn vào avatar
function toggleDropdown(event) {
    event.stopPropagation(); // Ngừng sự kiện lan truyền để không bị đóng khi click vào button
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('show'); // Thêm hoặc xóa lớp 'show' để hiển thị/ẩn menu
}

// Đóng dropdown nếu nhấn ra ngoài dropdown hoặc avatar
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('user-dropdown');
    const button = document.getElementById('user-menu-button');
    // Nếu người dùng nhấn ra ngoài dropdown và avatar thì ẩn dropdown
    if (!button.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
</script>