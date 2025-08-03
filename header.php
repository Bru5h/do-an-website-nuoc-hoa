<nav class="nav">
    <div class="nav-container">
        <a href="home.php" class="logo">
            <span>Cửa Hàng Nước Hoa</span>
        </a>
        <div id="mega-menu" class="mega-menu">
            <ul class="menu-list">
                <li><a href="home.php" class="menu-item">Trang chủ</a></li>
                <li>   
                    <div id="sidebar" class="sidebar hidden">
                        <div class="sidebar-content">
                            <ul class="sidebar-list">
                                <li><a href="san-pham-moi.php" class="sidebar-item">Sản phẩm mới</a></li>
                                <li><a href="giam-gia.php" class="sidebar-item">Giảm giá</a></li>
                                <li><a href="ban-chay.php" class="sidebar-item">Bán chạy</a></li>
                                <li><a href="phan-loai.php?query=Chanel" class="sidebar-item">Chanel</a></li>
                                <li><a href="phan-loai.php?query=Dior" class="sidebar-item">Dior</a></li>
                                <li><a href="phan-loai.php?query=Yves Saint Laurent" class="sidebar-item">Yves Saint Laurent</a></li>
                                <li><a href="phan-loai.php?query=Gucci" class="sidebar-item">Gucci</a></li>
                                <li><a href="phan-loai.php?query=Lancôme" class="sidebar-item">Lancôme</a></li>
                                <li><a href="phan-loai.php?query=Tom Ford" class="sidebar-item">Tom Ford</a></li>
                                <li><a href="phan-loai.php?query=Jo Malone London" class="sidebar-item">Jo Malone London</a></li>
                                <li><a href="phan-loai.php?query=Marc Jacobs" class="sidebar-item">Marc Jacobs</a></li>
                                <li><a href="phan-loai.php?query=Viktor & Rolf " class="sidebar-item">Viktor & Rolf </a></li>
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
                <li><a href="lien-he.php" class="menu-item">Liên hệ</a></li>
                <li class="search-container">
                    <form action="tim-kiem.php" method="get">
                        <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." class="search-input">
                        <button type="submit" class="find-btn"><i class="fas fa-search"></i></button>
                    </form>
                </li>
            </ul>
        </div>
        <div class="nav-buttons">           
            <button id="loginBtn" class="register-login-btn" type="button">
                <span class="tooltip-text">Đăng nhập</span>
            </button>
        </div>
    </div>
</nav>
  

<div id="loginModal" class="modal">
    <div class="modal-content">
        <span id="closeModal" class="close">&times;</span>
        <h2>Đăng nhập</h2>
        <form method="post" action="dang-nhap.php">
            <div class="input-box">
                Username: 
                <input type="text" name="tendangnhap" placeholder="Tên đăng nhập" required>
            </div>
            <div class="input-box">
                Password: 
                <input type="password" name="matkhau" placeholder="Mật khẩu" required>
            </div>
            <button class="Btn-login" id="loginBtn" type="submit">Đăng nhập</button>
        </form>
        <div class="register-link">
            <p>Không có tài khoản? <a href="#" id="showRegisterModal">Đăng ký</a></p>
            <p>Quên mật khẩu? <a href="#" id="showForgotPasswordModal">Khôi phục mật khẩu</a></p>
        </div>
    </div>
</div>

<div id="registerModal" class="modal">
    <div class="modal-content">
        <span id="closeRegisterModal" class="close">&times;</span>
        <h2>Đăng ký</h2>
        <form method="post" action="dang-ky.php">
            <div class="input-box">
                Họ tên
                <input type="text" name="hoten" placeholder="Họ và tên" required>
            </div>
            <div class="input-box">
                Email
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-box">
                Số điện thoại
                <input type="tel" name="sdt" pattern="[0-9]{10,11}" maxlength="11" placeholder="Số điện thoại" required>
            </div>
            <div class="input-box">
                Username
                <input type="text" name="tendangnhap" placeholder="Tên đăng nhập" required>
            </div>
            <div class="input-box">
                Password
                <input type="password" name="matkhau" placeholder="Mật khẩu" required>
            </div>
            <div class="input-box">
                 Địa chỉ
             <input type="text" name="diachi" placeholder="Nhập địa chỉ" required>
</div>
            
            <button class="Btn-register" id="registerBtn" type="submit">Đăng ký</button>
        </form>
        <div class="login-link">
            <p>Đã có tài khoản? <a href="#" id="showLoginModal">Đăng nhập</a></p>
        </div>
    </div>
</div>

<div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
        <span id="closeForgotPasswordModal" class="close">&times;</span>
        <h2>Khôi phục mật khẩu</h2>
        <form method="post" action="quen-mat-khau.php">
            <div class="input-box">
                Email:
                <input type="email" name="email" placeholder="Nhập email đã đăng ký" required>
            </div>
            <button class="Btn-login" type="submit">Gửi yêu cầu</button>
        </form>
        <div class="login-link">
            <p>Đã nhớ mật khẩu? <a href="#" id="backToLoginModal">Đăng nhập</a></p>
        </div>
    </div>
</div>
<script>
    // Lấy các phần tử modal và nút
    var loginModal = document.getElementById("loginModal");
    var loginBtn = document.getElementById("loginBtn");
    var closeLoginModal = document.getElementById("closeModal");

    var registerBtn = document.getElementById("registerBtn");
    var registerModal = document.getElementById("registerModal");
    var closeRegisterModal = document.getElementById("closeRegisterModal");

    var forgotPasswordModal = document.getElementById("forgotPasswordModal");
    var showForgotPasswordModal = document.getElementById("showForgotPasswordModal");
    var closeForgotPasswordModal = document.getElementById("closeForgotPasswordModal");
    var backToLoginModal = document.getElementById("backToLoginModal");

    var showRegisterModal = document.getElementById("showRegisterModal");
    var showLoginModal = document.getElementById("showLoginModal");

    // Mở modal đăng nhập
    loginBtn.onclick = function() {
        loginModal.style.display = "block";
    }

    // Đóng modal đăng nhập
    closeLoginModal.onclick = function() {
        loginModal.style.display = "none";
    }

    // Mở modal đăng ký
    registerBtn.onclick = function() {
        registerModal.style.display = "block"; // Mở modal đăng ký
    }

    // Đóng modal đăng ký
    closeRegisterModal.onclick = function() {
        registerModal.style.display = "none";
    }

    // Mở modal quên mật khẩu
    showForgotPasswordModal.onclick = function() {
        loginModal.style.display = "none"; // Đóng modal đăng nhập
        forgotPasswordModal.style.display = "block"; // Mở modal quên mật khẩu
    }

    // Đóng modal quên mật khẩu
    closeForgotPasswordModal.onclick = function() {
        forgotPasswordModal.style.display = "none";
    }

    // Quay lại modal đăng nhập từ quên mật khẩu
    backToLoginModal.onclick = function() {
        forgotPasswordModal.style.display = "none";
        loginModal.style.display = "block";
    }

    // Mở modal đăng nhập từ modal đăng ký
    showLoginModal.onclick = function() {
        registerModal.style.display = "none"; // Đóng modal đăng ký nếu đang mở
        loginModal.style.display = "block"; // Mở modal đăng nhập
    }

    showRegisterModal.onclick = function() {
        loginModal.style.display = "none";
        registerModal.style.display = "block";
    }
    // Đóng modal khi nhấp bên ngoài
    window.onclick = function(event) {
        if (event.target == loginModal) {
            loginModal.style.display = "none";
        } else if (event.target == registerModal) {
            registerModal.style.display = "none";
        } else if (event.target == forgotPasswordModal) {
            forgotPasswordModal.style.display = "none";
        }
    }

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

// Xử lý sidebar
window.onclick = function(event) {
    if (event.target == overlay) {
        sidebar.classList.remove("show");
        overlay.classList.remove("show");
        document.body.classList.remove("sidebar-open");
    }
};
</script>