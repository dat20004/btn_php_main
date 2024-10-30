<?php
session_start();
require 'connect.php'; // Kết nối đến cơ sở dữ liệu

if (isset($_POST['cart_id'])) {
    $cart_id = htmlspecialchars($_POST['cart_id']);
    
    // Xóa sản phẩm khỏi giỏ hàng
    $deleteStmt = $pdo->prepare("DELETE FROM carts WHERE id = ?");
    $deleteStmt->execute([$cart_id]);

    // Làm mới trang trước
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

?>
