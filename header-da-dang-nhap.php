<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (isset($_SESSION['tendangnhap'])) {
    $tendangnhap = $_SESSION['tendangnhap'];
    $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Lấy thông tin người dùng từ cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT idnguoidung, hoten, sdt, email, diachi FROM nguoidung WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $idnguoidung = $user['idnguoidung'];
} else {
    $user = null; 
}
?>


<nav class="nav">
    <div class="nav-container">
        <a href="home-da-dang-nhap.php" class="logo">
            <span>Cửa Hàng Nước Hoa</span>
        </a>
        <div id="mega-menu" class="mega-menu">
            <ul class="menu-list">
                <li><a href="home-da-dang-nhap.php" class="menu-item">Trang chủ</a></li>
                <li>   
                <div class="sidebar" id="sidebar">
                    <div class="sidebar-content">
                        <ul class="sidebar-list">
                            <li><a href="san-pham-moi-ddn.php" class="sidebar-item">Sản phẩm mới</a></li>
                            <li><a href="giam-gia-ddn.php" class="sidebar-item">Giảm giá</a></li>
                            <li><a href="ban-chay-ddn.php" class="sidebar-item">Bán chạy</a></li>
                            <li><a href="phan-loai-ddn.php?query=Chanel" class="sidebar-item">Chanel</a></li>
                                <li><a href="phan-loai-ddn.php?query=Dior" class="sidebar-item">Dior</a></li>
                                <li><a href="phan-loai-ddn.php?query=Yves Saint Laurent" class="sidebar-item">Yves Saint Laurent</a></li>
                                <li><a href="phan-loai-ddn.php?query=Gucci" class="sidebar-item">Gucci</a></li>
                                <li><a href="phan-loai-ddn.php?query=Lancôme" class="sidebar-item">Lancôme</a></li>
                                <li><a href="phan-loai-ddn.php?query=Tom Ford" class="sidebar-item">Tom Ford</a></li>
                                <li><a href="phan-loai-ddn.php?query=Jo Malone London" class="sidebar-item">Jo Malone London</a></li>
                                <li><a href="phan-loai-ddn.php?query=Marc Jacobs" class="sidebar-item">Marc Jacobs</a></li>
                                <li><a href="phan-loai-ddn.php?query=Viktor & Rolf " class="sidebar-item">Viktor & Rolf </a></li>
                        </ul>
                    </div>
                </div>

                    <div id="overlay" class="overlay hidden"></div>
                    <div class="menu-toggle">
                        <button id="openSidebar" class="danh-muc">
                            Danh mục
                            <svg class="menu-toggle-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 4"></svg>
                        </button>
                    </div>
                </li>
                <li><a href="lien-he-da-dang-nhap.php" class="menu-item">Liên hệ</a></li>
                <li class="search-container">
                    <form action="tim-kiem.php" method="get">
                        <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." class="search-input">
                        <button type="submit" class="find-btn"><i class="fas fa-search"></i></button>
                    </form>
                </li>
            </ul>
        </div>
        <div class="nav-buttons"> 
            <a href="dat-hang.php" class="register-login-btn"> <i class="fas fa-shopping-cart"></i> Giỏ hàng </a> 
            <div style="position: relative;">
            <button type="button" id="user-menu-button">
    <i class="fas fa-user-circle" style="font-size:28px;"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="user-dropdown" style="display: none;">
                <div>
                    <span><?= $user['hoten'] ?></span>
                    <span><?= $user['sdt'] ?></span>
                </div>
                <ul>
                    <li>
                        <a href="javascript:void(0)" onclick="openUserInfoModal()"> Thay đổi thông tin người dùng</a>
                    </li>
                    <li>
                        <a href="don-hang-cua-toi.php">Đơn hàng của tôi</a>
                    </li>
                    <li>
                        <a href="home.php">Đăng xuất</a>
                    </li>
                </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<div id="userInfoModal" class="modal-userinformation">
    <div class="modal-content">
        <div class="flex justify-between">
            <h2 class="text-2xl">Thông Tin Người Dùng</h2>
            <button onclick="closeUserInfoModal()" class="close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="userInfoForm" action="thaydoithongtin.php" method="POST">
            <input type="hidden" name="idnguoidung" value="<?= $idnguoidung ?>">
            
            <div class="input-box">
                <label for="hoten">Họ và tên</label>
                <input type="text" name="hoten" id="hoten" required value="<?= $user['hoten'] ?>">
            </div>

            <div class="input-box">
                <label for="sdt">Số điện thoại</label>
                <input type="text" name="sdt" id="sdt" required value="<?= $user['sdt'] ?>">
            </div>
            
            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required value="<?= $user['email'] ?>">
            </div>

            <div class="input-box">
                <label for="old-password">Mật khẩu cũ</label>
                <input type="password" name="old-password" id="old-password" required>
            </div>

            <div class="input-box">
                <label for="new-password">Mật khẩu mới</label>
                <input type="password" name="new-password" id="new-password" required>
            </div>

            <div class="input-box">
                <label for="confirm-password">Nhập lại mật khẩu</label>
                <input type="password" name="confirm-password" id="confirm-password" required>
            </div>
           <div class="input-box">
    <label for="diachi">Địa chỉ</label>
    <input type="text" name="diachi" id="diachi" required value="<?= htmlspecialchars($user['diachi']) ?>">
</div>


            <div class="btn-container">
                <button type="submit" class="Btn-login">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');
    const userInfoModal = document.getElementById('userInfoModal');
    const overlay = document.getElementById('overlay'); 


    userMenuButton.addEventListener('click', function () {
        const isVisible = userDropdown.style.display === 'block';
        if (isVisible) {
            userDropdown.style.display = 'none';
        } else {
            userDropdown.style.display = 'block';
        }
    });

    window.addEventListener('click', function (event) {
        if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.style.display = 'none';
        }
    });

    function closeUserInfoModal() {
        userInfoModal.style.display = 'none';
        overlay.style.display = 'none'; 
    }

    window.openUserInfoModal = function () {
        userInfoModal.style.display = 'flex';
        overlay.style.display = 'block'; 
    }

    userInfoModal.addEventListener('click', function(event) {
        event.stopPropagation(); 
    });

    const closeButton = document.querySelector('.close');
    closeButton.addEventListener('click', function () {
        closeUserInfoModal(); 
    });
});
var openSidebar = document.getElementById("openSidebar");
var sidebar = document.getElementById("sidebar");
var overlay = document.getElementById("overlay");

openSidebar.onclick = function() {
    sidebar.classList.add("show");
    overlay.classList.add("show");
    document.body.classList.add("sidebar-open"); 
};

overlay.onclick = function() {
    sidebar.classList.remove("show");
    overlay.classList.remove("show");
    document.body.classList.remove("sidebar-open"); 
};

window.onclick = function(event) {
    if (event.target == overlay) {
        sidebar.classList.remove("show");
        overlay.classList.remove("show");
        document.body.classList.remove("sidebar-open");
    }
};
</script>
