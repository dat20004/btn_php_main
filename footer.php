<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/533aad8d01.js" crossorigin="anonymous"></script>
</head>

<body>
<footer class="footer">
    <div class="container">
        <div class="inner-wrap">
            <div class="footer-first">
                <ul>
                    <li>FastLearn - Nhanh chóng, Hiệu quả, Vươn xa!</li>
                    <li><i class="fa-solid fa-location-dot"></i> New York</li>
                    <li><i class="fa-regular fa-envelope"></i> Fastlearn.work@gmail.com</li>
                </ul>
            </div>
            <div class="footer-second">
                <ul>
                    <li>Giới thiệu</li>
                    <li><a class="dropdown-item" href="hello-fastlearn.php">Về FastLearn</a></li>
                    <li><a class="dropdown-item" href="policy.php">Chính sách bảo mật</a></li>
                    <li><a class="dropdown-item" href="terms-service.php">Điều khoản dịch vụ</a></li>
                    <li><a class="dropdown-item" href="payment-policy.php">Quy định</a></li>
                </ul>
            </div>
            <div class="footer-last">
                <ul>
                    <li>Kết nối với chúng tôi</li>
                    <li><img src="./images/youtube.png" alt="">
                        <img src="./images/instagram.png" alt="">
                        <img src="./images/twitter.png" alt="">
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<script>
function toggleActionButtons(element) {
    // Tìm phần tử hành động gần nhất và bật/tắt hiển thị
    var actionButtons =
        element.parentElement.parentElement.querySelector(
            ".action-buttons"
        );
    if (actionButtons.style.display === "block") {
        actionButtons.style.display = "none";
    } else {
        actionButtons.style.display = "block";
    }
}

// Ẩn hành động khi click ngoài vùng
document.addEventListener("click", function(event) {
    var isClickInside = event.target.closest(
        ".three-dots, .action-buttons"
    );
    if (!isClickInside) {
        var actionButtons =
            document.querySelectorAll(".action-buttons");
        actionButtons.forEach(function(buttons) {
            buttons.style.display = "none";
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
</body>

</html>