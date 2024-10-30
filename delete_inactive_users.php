<?php
require 'connect.php'; // Kết nối CSDL

// Câu lệnh SQL để xóa tài khoản không đăng nhập trong 6 tháng
$stmt = $conn->prepare("DELETE FROM users WHERE last_login IS NULL OR last_login < NOW() - INTERVAL 6 MONTH");
$stmt->execute();

echo "Đã xóa các tài khoản không đăng nhập trong 6 tháng qua.";
?>
