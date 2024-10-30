<?php
ob_start();  // Bắt đầu output buffering

// Kiểm tra trạng thái session và khởi động nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Chỉ khởi động session nếu chưa có
}

// Kết nối tới cơ sở dữ liệu
include '../connect.php';  

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    header('Location: ../login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Lấy ID người dùng từ session
$user_id = $_SESSION['userID'];

// Truy vấn để lấy thông tin người dùng từ bảng `users`
$query = "SELECT id, full_name, avatar, role FROM users WHERE id = ?"; 
$stmt = $pdo->prepare($query); // Chuẩn bị truy vấn
$stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Bind tham số ID người dùng
$stmt->execute(); // Thực hiện truy vấn
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy thông tin người dùng
$stmt->closeCursor(); // Đóng cursor để giải phóng kết nối

// Kiểm tra xem người dùng có tồn tại không
if (!$user) {
    header('Location: login.php'); // Chuyển hướng nếu không tìm thấy người dùng
    exit;
}

// Thiết lập đường dẫn đến ảnh đại diện, đảm bảo có '/' ở giữa
$imagePath = './' . htmlspecialchars($user['avatar']);

// Kiểm tra vai trò của người dùng
if ($user['role'] !== 'Admin') {  // Thay 'Admin' bằng vai trò bạn muốn kiểm tra
    header('Location: ../login.php'); // Chuyển hướng đến trang không được phép nếu vai trò không đúng
    exit; 
}


// Nếu cần, bạn có thể sử dụng những thông tin dưới đây trong HTML
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>FastLearn</title>
    <link rel="icon" href="../images/logoweb.png" type="image/png">

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">

    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/summernote/summernote.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
    <style>
    /* Định dạng dropdown-menu */
    .dropdown-menu {
        display: none;
        /* Ẩn menu mặc định */
        position: absolute;
        top: 100%;
        /* Đặt menu xuống dưới nút */
        left: 0;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        padding: 10px;
        min-width: 200px;
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease;
        /* Hiệu ứng mượt mà */
    }

    /* Khi menu được hiển thị */
    .dropdown-menu.show {
        display: block;
        opacity: 1;
        visibility: visible;
    }

    /* Định dạng danh sách trong menu */
    .dropdown-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .dropdown-menu ul li {
        padding: 8px 10px;
    }

    .dropdown-menu ul li a {
        color: #333;
        text-decoration: none;
        display: block;
    }

    .dropdown-menu ul li a:hover {
        background-color: #f0f0f0;
        color: #007bff;
    }

    /* Định dạng khi hover nút */
    .btn-icon:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
    }

    /* Định dạng chung cho các mục trong dropdown */

    /* Định dạng chung cho các mục trong dropdown */
    .dropdown-item {
        padding: 10px 15px;
        font-size: 16px;
        color: #333;
        display: flex;
        align-items: center;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Định dạng icon trong mục "Đăng xuất" */
    .dropdown-item i {
        margin-left: 8px;
        /* Khoảng cách giữa chữ và icon */
        font-size: 18px;
        /* Kích thước của icon */
        transition: color 0.3s ease;
    }

    /* Hiệu ứng khi hover vào "Đăng xuất" */
    .dropdown-item:hover {
        background-color: #f0f0f0;
        color: #007bff;
    }

    .dropdown-item:hover i {
        color: #007bff;
        /* Màu của icon khi hover */
    }

    .avatar-image {
        width: 35px;
        /* Kích thước hình ảnh, có thể tùy chỉnh */
        height: 35px;
        /* Đảm bảo chiều rộng và chiều cao bằng nhau để tạo hình tròn */
        border-radius: 50%;
        /* Làm tròn các góc để tạo thành hình tròn */
        object-fit: cover;
        /* Đảm bảo hình ảnh được cắt đúng kích thước, tránh méo ảnh */
    }

    .thognke {
        background: red;
    }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">

        <header class="main-header header-h">
            <!-- Logo -->
            <a href="index.php">
                <div class="header-img"><img src="../images/logoweb.png" alt=""></div>
            </a>
            <div class="header-input">
                <i class="fa-solid fa-magnifying-glass"></i><input type="text" name="" placeholder="Tìm kiếm">
            </div>
            <div class="header-icon">
                <ul>
                    <li>
                    <div class="dropdown">
    <button class="btn btn-icon dropdown-toggle" type="button" id="notificationButton" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-bell"></i> <!-- Icon thông báo -->
    </button>
    <ul class="dropdown-menu" aria-labelledby="notificationButton">
        <!-- <li>
            <h6 class="dropdown-header">Thông báo mới nhất</h6>
        </li> -->
        <?php
        // Lấy phản hồi từ cơ sở dữ liệu
        $stmt = $pdo->prepare("
            SELECT 
                cf.feedback, 
                cf.feedback_date, 
                u.full_name, 
                c.name AS course_name 
            FROM 
                course_feedbacks cf
            JOIN 
                users u ON cf.student_id = u.id 
            JOIN 
                courses c ON cf.course_id = c.id 
            ORDER BY 
                cf.feedback_date DESC
            LIMIT 5; -- Giới hạn số lượng phản hồi hiển thị
        ");
        $stmt->execute();
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (!empty($feedbacks)): ?>
            <?php foreach ($feedbacks as $feedback): ?>
                <li>
                    <a class="dropdown-item" href="#">
                        <strong><?php echo htmlspecialchars($feedback['full_name']); ?></strong> - 
                        <span><?php echo htmlspecialchars($feedback['course_name']); ?></span><br>
                        <?php echo nl2br(htmlspecialchars($feedback['feedback'])); ?><br>
                        <small class="text-muted">
                            <?php 
                                // Format the feedback_date
                                $date = new DateTime($feedback['feedback_date']);
                                echo htmlspecialchars($date->format('d/m/Y')); 
                            ?>
                        </small>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><a class="dropdown-item" href="#">Không có thông báo nào.</a></li>
        <?php endif; ?>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="feedback.php">Đọc tất cả thông báo</a></li>
    </ul>
</div>

                    </li>
                    <li>
                        <div class="dropdown">
                            <button class="btn btn-avatar dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if ($user && !empty($user['avatar'])): ?>
                                <?php if (file_exists($imagePath)): // Kiểm tra tệp hình ảnh có tồn tại ?>
                                <img src="<?php echo $imagePath; ?>" alt="" class="avatar-image">
                                <?php else: ?>
                                <p>Hình ảnh không tồn tại.</p>
                                <?php endif; ?>
                                <?php else: ?>
                                <p>Không có hình ảnh nào.</p>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="margin-top :15px;">
                                <li><a class="dropdown-item" href="myProfile.php ">Thông tin tài khoản</a></li>
                                <li><a class="dropdown-item" href="settingsPassword.php">Cài đặt</a></li>
                                <hr>
                                <li><a class="dropdown-item" href="logout.php"> Đăng xuất <i
                                            class="fa-solid fa-sign-out-alt"></i></a></li>

                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

        </header>
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->

                <hr>
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu">
                    <li>
                        <a href="index.php">
                            <i class="fa fa-home"></i> <span>Quản lý nội dung</span> <i
                                class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="course.php"><i class="fa fa-circle-o"></i>Khóa học</a></li>
                            <li><a href="teacher.php"><i class="fa fa-circle-o"></i>Giảng viên</a></li>
                            <li><a href="feedback.php"><i class="fa fa-circle-o"></i>Đánh giá</a></li>
                            <li><a href="major.php"><i class="fa fa-circle-o"></i>Ngành</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-th"></i> <span>Quản lý tài khoản</span> <i
                                class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="account.php"><i class="fa fa-circle-o"></i>Tài khoản</a></li>
                            <li><a href="role-decentralization.php"><i class="fa fa-circle-o"></i>Vai trò và phân
                                    quyền</a>
                            </li>
                            <li><a href="manager-notifi.php"><i class="fa fa-circle-o"></i>Quản lý thông báo</a>
                            </li>
                        </ul>
                    </li>
                    <!-- <li class="treeview">
                        <a href="#">
                            <i class="fa fa-th"></i> <span>Quản lý đơn hàng</span> <i
                                class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="list-account.php"><i class="fa fa-circle-o"></i> Danh sách đơn hàng</a></li>
                            <li><a href="manager-cod.php"><i class="fa fa-circle-o"></i>Quản lý COD</a></li>
                            <li><a href="edit-cod.php"><i class="fa fa-circle-o"></i>Xử lý COD</a></li>
                            <li><a href="call-customer.php"><i class="fa fa-circle-o"></i>Liên hệ khách hàng</a></li>
                        </ul>
                    </li> -->

                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-user"></i> <span>Thống kê</span> <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="statistics.php"><i class="fa fa-circle-o"></i>Thống kê chung</a></li>                        </ul>
                    </li>
                    <hr>
                    <li><a class="dropdown-item" href="logout.php"> <button style="display: flex;
    align-items: center;" class="btn btn-success mt-3"> Đăng xuất <i class="fa-solid fa-sign-out-alt"></button></i></a>
                    </li>

                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
        <script>
        $(document).ready(function() {
            // Khi nhấn vào nút thông báo, hiển thị hoặc ẩn menu
            $('#notificationButton').on('click', function() {
                // Ẩn menu giỏ hàng nếu nó đang mở
                $('#cartButton').next('.dropdown-menu').removeClass('show');
                // Toggle menu thông báo
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