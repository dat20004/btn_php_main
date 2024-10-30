<?php
// session_start();
include 'header.php';
include 'connect.php'; // Bao gồm tệp kết nối



// Kiểm tra xem người dùng có giỏ hàng trong phiên hay không
$userId = $_SESSION['userID']; // Giả sử bạn đã lưu user_id trong session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Khởi tạo một mảng trống để lưu trữ các mặt hàng lấy từ cơ sở dữ liệu
$db_cart_items = [];

if ($userId) {
    // Chuẩn bị truy vấn để lấy thông tin khóa học và số lượng từ bảng carts
    $sql = "
        SELECT c.id AS course_id, c.name, c.fee, ca.quantity 
        FROM courses c 
        JOIN carts ca ON c.id = ca.course_id 
        WHERE ca.user_id = :userID
    ";
    
    // Chuẩn bị và thực thi câu lệnh
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userID' => $userId]);
    
    // Lấy tất cả các mặt hàng và tính tổng
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $db_cart_items[] = [
            'id' => $item['course_id'],
            'name' => $item['name'],
            'fee' => $item['fee'],
            'quantity' => $item['quantity'], // Lấy số lượng từ bảng carts
            'total' => $item['fee'] * $item['quantity']
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Giỏ hàng của bạn</h2>

    <?php if (!empty($db_cart_items)): ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Tên khóa học</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Tổng</th>
            </tr>
            </thead>
            <tbody>
            <?php 
            $total = 0;
            foreach ($db_cart_items as $item): 
                $total += $item['total'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['fee'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['total'], 0, ',', '.'); ?>đ</td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Tổng cộng</strong></td>
                <td><strong><?php echo number_format($total, 0, ',', '.'); ?>đ</strong></td>
            </tr>
            </tbody>
        </table>
        <a href="thanhtoan.php"><button class="btn btn-success">Tiến hành thanh toán</button></a>
    <?php else: ?>
        <p>Giỏ hàng của bạn đang trống.</p>
    <?php endif; ?>
</div>
</body>
</html>