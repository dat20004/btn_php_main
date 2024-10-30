<?php
ob_start();
session_start();
include 'header.php';
include 'connect.php';

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['userID'])) {
    echo "Vui lòng đăng nhập để xem chi tiết khóa học.";
    exit;
}

$student_id = $_SESSION['userID']; // Lấy ID của sinh viên từ session
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kiểm tra nếu ID khóa học hợp lệ
if ($course_id > 0) {
    // Truy vấn để lấy thông tin khóa học từ cơ sở dữ liệu
    $sql = "SELECT name, fee FROM courses WHERE id = :courseId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':courseId', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra xem khóa học có tồn tại không
    if (!$course) {
        $_SESSION['error'] = "Khóa học không tồn tại.";
        header("Location: homemain.php");
        exit();
    }

    // Nếu khóa học tồn tại, gán giá trị
    $courseName = $course['name'];
    $coursePrice = preg_replace('/\D/', '', $course['fee']); // Loại bỏ ký tự không phải số

    // Xử lý khi người dùng gửi form
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Lấy và kiểm tra dữ liệu từ form
        $name = !empty($_POST['name']) ? $_POST['name'] : '';
        $email = !empty($_POST['email']) ? $_POST['email'] : '';
        $phone = !empty($_POST['phone']) ? $_POST['phone'] : '';
        $address = !empty($_POST['address']) ? $_POST['address'] : '';
        $paymentMethod = !empty($_POST['payment_method']) ? $_POST['payment_method'] : '';

        // Kiểm tra dữ liệu đầu vào
        if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($paymentMethod)) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin.";
        } else {
            // Thực hiện thanh toán (giả lập thành công)

            // Thêm thông tin đăng ký vào bảng course_enrollments
            $enroll_sql = "INSERT INTO course_enrollments (student_id, course_id, enroll_date) 
            VALUES (:studentId, :courseId, NOW())";
            $enroll_stmt = $pdo->prepare($enroll_sql);
            $enroll_stmt->bindParam(':studentId', $student_id, PDO::PARAM_INT);
            $enroll_stmt->bindParam(':courseId', $course_id, PDO::PARAM_INT);

            if ($enroll_stmt->execute()) {
            $_SESSION['success'] = "Thanh toán thành công! Bạn đã đăng ký khóa học: " . htmlspecialchars($courseName);
            header("Location: homemain.php"); // Chuyển hướng sau khi thanh toán thành công
            exit();
            } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi đăng ký khóa học.";
            }
        }
    }
} else {
    echo "Khóa học không hợp lệ.";
    exit;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Khóa Học</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Thanh Toán Khóa Học</h2>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        <h4><?php echo htmlspecialchars($courseName); ?> - Giá:
            <?php echo number_format((float)$coursePrice, 0, ',', '.'); ?> VNĐ</h4>

        

        <form action="" method="POST" class="mt-4">
            <div class="form-group">
                <label for="name">Họ và tên:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Điện thoại:</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ:</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label>Phương thức thanh toán:</label>
                <select class="form-control" name="payment_method" required>
                    <option value="">Chọn phương thức</option>
                    <option value="credit_card">Thẻ tín dụng</option>
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Thanh Toán</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Quay lại</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>