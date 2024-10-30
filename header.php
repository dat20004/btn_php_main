<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'connect.php';  // Kết nối tới cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); 
    exit;
}


// Lấy ID người dùng từ session
$user_id = $_SESSION['userID']; 

// Lấy thông tin giỏ hàng của người dùng từ cơ sở dữ liệu
if (isset($_SESSION['userID'])) {
    $user_id = $_SESSION['userID'];

    $stmt = $pdo->prepare("SELECT * FROM carts WHERE user_id = :userID");
    $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cart_items = []; 
}


// Truy vấn để lấy thông tin người dùng
$query = "SELECT * FROM users WHERE id = ?"; 
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Sử dụng PDO để bind param
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy thông tin người dùng
$stmt->closeCursor(); // Đóng cursor để giải phóng kết nối

// Kiểm tra nếu người dùng tồn tại và có ảnh đại diện
$imagePath = htmlspecialchars($user['avatar']);

// Truy vấn để lấy danh sách chuyên ngành và khóa học
$stmt = $pdo->prepare("SELECT majors.id AS major_id, majors.name AS major_name, courses.id AS course_id, courses.name AS course_name 
                        FROM majors 
                        LEFT JOIN courses ON majors.id = courses.major_id");
$stmt->execute();



// Tạo mảng để chứa dữ liệu majors và khóa học
$majors = [];

// Lấy dữ liệu từ cơ sở dữ liệu và tổ chức thành mảng
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $majors[$row['major_id']]['name'] = $row['major_name']; // Lưu tên ngành
    $majors[$row['major_id']]['courses'][] = [
        'id' => $row['course_id'],
        'name' => $row['course_name']
    ]; // Lưu danh sách khóa học theo ngành
}

// Kiểm tra vai trò người dùng
if ($user['role'] !== 'Student') {  // Thay 'Admin' bằng vai trò bạn muốn kiểm tra
    header('Location: logout.php'); // Chuyển hướng đến trang không được phép nếu vai trò không đúng
    exit; 
}
try {
    // Truy vấn lấy thông tin thông báo cùng với tên người đăng
    $sql = "SELECT posts.title, posts.created_at, users.full_name
            FROM posts
            JOIN users ON posts.admin_id  = users.id
            ORDER BY posts.created_at DESC
            LIMIT 5"; // Lấy 5 thông báo mới nhất
    
    // Chuẩn bị và thực thi truy vấn
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Lấy dữ liệu
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lặp qua mỗi thông báo để thêm thời gian đã trôi qua
    foreach ($notifications as &$notification) {
        // Tính thời gian đã trôi qua kể từ khi thông báo được tạo
        $notification['time_ago'] = timeAgo($notification['created_at']);
    }

    // Hiển thị dữ liệu (Ví dụ)
   

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Hàm tính thời gian đã trôi qua
function timeAgo($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    if ($time_difference < 1 ) return 'Vừa mới';

    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds < 60) {
        return "$seconds giây trước";
    } else if ($minutes < 60) {
        return "$minutes phút trước";
    } else if ($hours < 24) {
        return "$hours giờ trước";
    } else if ($days < 7) {
        return "$days ngày trước";
    } else if ($weeks < 4.3) {
        return "$weeks tuần trước";
    } else if ($months < 12) {
        return "$months tháng trước";
    } else {
        return "$years năm trước";
    }
}
$timeout_duration = 3; 

if (isset($_SESSION['last_activity'])) {
    $time_inactive = time() - $_SESSION['last_activity'];
    if ($time_inactive > $timeout_duration) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$_SESSION['last_activity'] = time();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
$user_logged_in = isset($_SESSION['user_login']) ? true : false;



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastLearn</title>
    <link rel="icon" href="./images/logoweb.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
</head>
<style>
.nav-item .dropdown-menu {
    padding: 0;
    margin-top: 17px;
    border: none;
    background-color: #fff;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    /* Đảm bảo dropdown cha có vị trí tương đối */
}

.header__dropdown__content--faculty {
    position: relative;
    /* Đảm bảo mục chính có vị trí tương đối */
    display: flex;
    flex-direction: column;
    /* Để các mục con được xếp chồng lên nhau */
    margin-right: 20px;
    /* Khoảng cách giữa các mục con */
    color: #2F4157;
    font-family: 'Poppins', sans-serif;
}

/* Định dạng cho các liên kết trong menu */
.header__dropdown__content--faculty--link {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    color: black;
}

/* Hiệu ứng khi hover vào mục cha */
.header__dropdown__content--faculty:hover {
    background-color: #f0f0f0;
}

/* Biểu tượng mũi tên bên phải */
.header__dropdown__content--faculty--link--image {
    width: 20px;
    transition: transform 0.3s ease;
}

/* Hiệu ứng khi hover vào mục cha */
.header__dropdown__content--faculty--link:hover .header__dropdown__content--faculty--link--image {
    transform: translateX(10%);
}

/* Sub-dropdown menu con (hiển thị khi hover vào mục chính) */
.header__dropdown__content--major {
    opacity: 0;
    visibility: hidden;
    position: absolute;
    left: 100%;
    /* Đẩy menu con sang bên phải */
    top: 0;
    /* Giữ vị trí top cùng mức với mục cha */
    /* margin-left: 21px; */
    /* Khoảng cách giữa menu chính và menu con */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    min-width: 150px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 1000;
}

.header__dropdown__content--major1 {
    opacity: 0;
    visibility: hidden;
    position: absolute;
    left: 100%;
    /* Đẩy menu con sang bên phải */
    bottom: 10%;
    /* Giữ vị trí top cùng mức với mục cha */
    margin-left: 21px;
    /* Khoảng cách giữa menu chính và menu con */
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    min-width: 150px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 1000;
}

.header__dropdown__content--faculty:hover .header__dropdown__content--major1 {
    opacity: 1;
    visibility: visible;
}

/* Hiển thị menu con khi hover vào mục chính */
.header__dropdown__content--faculty:hover .header__dropdown__content--major {
    opacity: 1;
    visibility: visible;
}

/* Link của mục con */
.header__dropdown__content--major--link a {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    font-size: 14px;
    color: #2F4157;
    font-family: 'Poppins', sans-serif;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Hiệu ứng khi hover vào link của mục con */
.header__dropdown__content--major--link a:hover {
    background-color: #577C8E;
    color: blue;
}

/* Biểu tượng mũi tên phải nhỏ */
.header__dropdown__content--major--link a img {
    width: 16px;
    margin-right: 8px;
}

.nav-item:hover .dropdown-menu {
    opacity: 1;
    /* Hiển thị khi hover */
    visibility: visible;
    /* Thay đổi visibility khi hover */
}


.header-buttons {
    display: flex;
    align-items: center;
    gap: 20px;

}


/* Phong cách chung cho các nút */
.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 10px 15px;
    background-color: #ffffff;
}

/* Notification Icon Button */
#notificationButton {
    color: #ffffff;
    font-size: 18px;
}

#notificationButton .fa-bell {
    transition: transform 0.3s ease;
}

#notificationButton:hover .fa-bell {
    transform: scale(1.1);
}

/* Notification Dropdown Content */
.dropdown-menu[aria-labelledby="notificationButton"] li {
    margin-bottom: 10px;
}

.dropdown-menu[aria-labelledby="notificationButton"] li .dropdown-item {
    color: #333;
    padding: 10px;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.dropdown-menu[aria-labelledby="notificationButton"] li .dropdown-item:hover {
    background-color: #f0f0f5;
}

.dropdown-menu[aria-labelledby="notificationButton"] hr {
    margin: 5px 0;
    border-color: #e0e0e0;
}

.dropdown-menu[aria-labelledby="notificationButton"] .dropdown-divider {
    margin: 10px 0;
}

/* Cart Icon Button */
#cartButton {
    background-color: #4739d1;
    color: #ffffff;
    font-size: 18px;
    border-radius: 8px;
    padding: 8px 10px;
    transition: all 0.3s ease;
    border: 2px solid transparent; /* Viền ban đầu trong suốt */
}

#cartButton:hover {
    border: 2px solid #ffffff; /* Thêm viền trắng khi hover */
}

#cartButton .fa-shopping-cart {
    transition: transform 0.3s ease;
}

#cartButton:hover .fa-shopping-cart {
    transform: scale(1.1);
}

/* Cart Dropdown Content */
.dropdown-menu[aria-labelledby="cartButton"] li {
    margin-bottom: 15px;
}

.dropdown-menu[aria-labelledby="cartButton"] li .dropdown-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background-color: #f7f8fa;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.dropdown-menu[aria-labelledby="cartButton"] li .dropdown-item:hover {
    background-color: #ebedf1;
}

.dropdown-menu[aria-labelledby="cartButton"] img {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    object-fit: cover;
    margin-right: 10px;
}

.dropdown-menu[aria-labelledby="cartButton"] .btn-danger {
    font-size: 14px;
    padding: 5px 10px;
    border-radius: 6px;
}

.dropdown-menu[aria-labelledby="cartButton"] .dropdown-divider {
    margin: 10px 0;
}

.dropdown-menu[aria-labelledby="cartButton"] .btn-primary {
    background-color: #4739d1;
    color: #fff;
    padding: 8px 12px;
    border-radius: 6px;
}

.dropdown-menu[aria-labelledby="cartButton"] .btn-primary:hover {
    background-color: #3b2fb5;
}


.btn-avatar {
    padding: 0;
    border: none;
    background-color: transparent;
}

.btn-avatar img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

.avatar-image {
    width: 100px;

    height: 100px;

    border-radius: 50%;

    object-fit: cover;

}

#cartButton+.dropdown-menu {
    transform: translateX(-40%);
    left: 0;
}
</style>


<body>
    <header class="header" styke="font-size: 18px;">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="homemain.php"><img class="header-img" src="./images/logoweb.png"
                        alt=""></a>

                <div class="collapse navbar-collapse d-flex justify-content-around" id="navbarNavDropdown">
                    <ul class="navbar-nav ">
                        <!-- Dropdown Ngành học -->


                        <!-- Dropdown Chương trình học -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="chuongTrinhHocDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false" style="color:white;">
                                Về chúng tôi
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="chuongTrinhHocDropdown"
                                style="padding: 0;margin-top:28px;">
                                <li>
                                    <a class="dropdown-item" href="hello-fastlearn.php">Về FastLearn </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="introduction-teacher.php">Đội ngũ giảng viên</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="feel-student.php">Học viên</a>
                                </li>
                            </ul>
                        </li>



                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="all-courses.php" id="giangVienDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:white;">
                                Ngành học
                            </a>
                            <ul class="dropdown-menu dropdown-teacher" aria-labelledby="giangVienDropdown"
                                style="padding: 0; margin-top: 28px;width: 230px;">
                                <?php foreach ($majors as $major_id => $major): ?>
                                <li class="header__dropdown__content--faculty" style="width:100%;">
                                    <a href="chitietcourses.php?id=<?php echo urlencode($major_id); ?>"
                                        class="header__dropdown__content--faculty--link">
                                        <span><?php echo htmlspecialchars($major['name']); ?></span>
                                        <img src="svg/alt-arrow-right.svg" alt=""
                                            class="header__dropdown__content--faculty--link--image">
                                    </a>
                                    <ul class="header__dropdown__content--major" style="width:100%;">
                                        <?php foreach ($major['courses'] as $course): ?>
                                        <li class="header__dropdown__content--major--link">
                                            <a href="course-content.php?id=<?php echo urlencode($course['id']); ?>">
                                                <span><?php echo htmlspecialchars($course['name']); ?></span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <?php endforeach; ?>
                            </ul>


                        </li>


                        </li>


                    </ul>

                    <!-- Tìm kiếm -->
                    <form class="d-flex header-form" role="search" style="width: 350px;">
                        <input class="form-control" type="search" placeholder="Tìm kiếm" aria-label="Search"
                            style="border-radius: 18px 0 0 18px;">
                        <button class="btn btn-outline-light" type="submit" style="border-radius: 0 18px 18px 0;"><i
                                class="fa-solid fa-magnifying-glass"></i></button>
                    </form>

                    <!-- Thay đổi giao diện tùy theo trạng thái đăng nhập -->
                    <div class="header-button__register" style="gap:1px;">
                        <!-- Nếu đã đăng nhập, hiển thị icon thông báo và avatar -->
                        <?php if ($user_logged_in): ?>
                        <div class="header-buttons d-flex align-items-center" style="gap:1px;">
                            <!-- Icon thông báo -->
                            <!-- Icon thông báo -->
                            <!-- Icon thông báo -->
                            <div class="dropdown">
                                <button class="btn btn-icon dropdown-toggle" type="button" id="notificationButton"
                                    data-bs-toggle="dropdown" aria-expanded="false" style="color: white;">
                                    <i class="fa-solid fa-bell"></i> <!-- Icon thông báo -->
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="notificationButton"
                                    style="max-height: 400px; overflow-y: auto;margin-top: 28px;">
                                    <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                    <li>
                                        <div class="dropdown-item">
                                            <strong><?php echo htmlspecialchars($notification['full_name']); ?></strong><br>
                                            <span><strong>Tiêu đề:</strong>
                                                <?php echo htmlspecialchars($notification['title']); ?></span><br>

                                            <!-- Nội dung đầy đủ của thông báo -->
                                            <small><em>Thời gian gửi:
                                                    <?php echo htmlspecialchars($notification['time_ago']); ?></em></small>
                                        </div>
                                        <hr>
                                    </li>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <li><a class="dropdown-item" href="#">Không có thông báo mới</a></li>
                                    <?php endif; ?>

                                    <!-- Mục "Đọc tất cả thông báo" -->
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="thongbao.php">Đọc tất cả thông báo</a>
                                    </li>
                                </ul>
                            </div>



                            <!-- Icon giỏ hàng -->
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="cartButton"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="background:#4739d1;border:none;">
                                    <i class="fa-solid fa-shopping-cart"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="cartButton" style="margin-top: 28px;">
                                    <?php
        // Lấy thông tin giỏ hàng của người dùng
        $stmt = $pdo->prepare("
            SELECT c.quantity, 
                   co.name AS course_name, 
                   co.image AS course_image, 
                   co.fee AS course_fee,
                   c.id AS cart_id  -- Lấy id giỏ hàng để xóa
            FROM carts c
            JOIN courses co ON c.course_id = co.id 
            WHERE c.user_id = :userID
        ");
        $stmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kiểm tra xem giỏ hàng có rỗng không
        if (!empty($cart_items)): ?>
                                    <?php foreach ($cart_items as $item): ?>
                                    <li class="dropdown-item d-flex align-items-center">
                                        <img src="./admin/<?php echo htmlspecialchars($item['course_image']); ?>"
                                            alt="Ảnh khóa học" class="me-2"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($item['course_name']); ?></strong><br>
                                            <span>Phí: <?php echo number_format($item['course_fee'], 0, ',', '.'); ?>
                                                VNĐ</span><br>
                                            <span>Số lượng: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                        </div>
                                        <form action="remove_from_cart.php" method="post" style="margin: 0;">
                                            <input type="hidden" name="cart_id"
                                                value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                style="margin-left: 10px;">Xóa</button>
                                        </form>
                                    </li>
                                    <hr class="dropdown-divider">
                                    <?php endforeach; ?>
                                    <li class="dropdown-item text-center">
                                        <a href="cart.php" class="btn btn-primary btn-sm">Xem giỏ hàng</a>
                                    </li>
                                    <?php else: ?>
                                    <li class="dropdown-item text-center">Giỏ hàng trống</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- Dropdown avatar -->
                            <div class="dropdown">
                                <button class="btn btn-avatar dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-expanded="false" style="color: white;">
                                    <?php 
                                     $defaultAvatar = './images/avatarmd.png';
                                    if ($user && !empty($user['avatar'])): ?>
                                    <?php if (file_exists($imagePath)): // Kiểm tra tệp hình ảnh có tồn tại ?>
                                    <img src="<?php echo $imagePath; ?>" alt="" class="avatar-image">
                                    <?php else: ?>
                                    <p>Hình ảnh không tồn tại.</p>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <img src="<?php echo $defaultAvatar; ?>" alt="Avatar mặc định" class="avatar-image">
                                    <?php endif; ?>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                                    style="margin-top :28px;">
                                    <li><a class="dropdown-item" href="myProfile.php">Thông tin tài khoản</a></li>
                                    <li><a class="dropdown-item" href="myCourses.php">Khoá học của tôi</a></li>
                                    <hr>
                                    <li><a class="dropdown-item" href="logout.php"> Đăng xuất <i
                                                class="fa-solid fa-sign-out-alt"></i></a></li>

                                </ul>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Nếu chưa đăng nhập, hiển thị nút Đăng ký và Đăng nhập -->
                        <a href="register.php">
                            <button class="btn btn-register">Đăng ký</button>
                        </a>
                        <a href="login.php">
                            <button class="btn btn-primary btn-login">Đăng nhập</button>
                        </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </nav>
    </header>


    <script>
    // Thêm jQuery để xử lý sự kiện click
    $(document).ready(function() {
        $('.header__dropdown__content--faculty--link').on('click', function(e) {
            e.preventDefault();
            // Ẩn tất cả menu con trước đó
            $('.header__dropdown__content--major').removeClass('visible').css({
                opacity: 0,
                visibility: 'hidden'
            });
            // Hiện menu con tương ứng
            $(this).siblings('.header__dropdown__content--major').toggleClass('visible').css({
                opacity: 1,
                visibility: 'visible'
            });
        });
    });
    $(document).ready(function() {
        // Khi nhấn vào nút thông báo, hiển thị hoặc ẩn menu
        $('#notificationButton').on('click', function() {
            // Ẩn menu giỏ hàng nếu nó đang mở
            $('#cartButton').next('.dropdown-menu').removeClass('show');
            // Toggle menu thông báo
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Khi nhấn vào nút giỏ hàng, hiển thị hoặc ẩn menu
        $('#cartButton').on('click', function() {
            // Ẩn menu thông báo nếu nó đang mở
            $('#notificationButton').next('.dropdown-menu').removeClass('show');
            // Toggle menu giỏ hàng
            $(this).next('.dropdown-menu').toggleClass('show');
        });

        // Khi nhấn ra ngoài các nút thông báo và giỏ hàng, ẩn các menu xổ xuống
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    });
    </script>
</body>

</html>