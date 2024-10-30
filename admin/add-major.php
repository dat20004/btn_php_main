<?php 
include 'header.php';
include '../connect.php';  // Kết nối với cơ sở dữ liệu

// Biến để lưu thông báo lỗi
$errors = [];
$success = "";  // Biến lưu thông báo thành công

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $major_name = trim($_POST['major_name']);
    $description = trim($_POST['description']);
    
    // Kiểm tra nếu tên ngành đã tồn tại trong cơ sở dữ liệu
    try {
        $check_query = "SELECT COUNT(*) FROM majors WHERE name = :major_name";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bindParam(':major_name', $major_name, PDO::PARAM_STR);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();
        
        if ($count > 0) {
            // Nếu ngành đã tồn tại, thông báo lỗi
            $errors[] = "Tên ngành '$major_name' đã tồn tại.";
        } else {
            // Xử lý file ảnh nếu có
            $image = null;  // Khởi tạo giá trị mặc định cho ảnh
            if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
                // Kiểm tra file upload có hợp lệ không
                $image_name = $_FILES['file_upload']['name'];
                $image_tmp = $_FILES['file_upload']['tmp_name'];
                $image_size = $_FILES['file_upload']['size'];
                $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

                // Các loại ảnh được phép tải lên
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($image_ext), $allowed_exts)) {
                    $errors[] = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
                }

                if ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 100MB
                    $errors[] = "Ảnh không được vượt quá 100MB.";
                }

                // Nếu ảnh hợp lệ, tiến hành lưu ảnh
                if (empty($errors)) {
                    $image_dir = 'uploads/';  // Thư mục lưu ảnh

                    // Kiểm tra thư mục uploads có tồn tại không, nếu không thì tạo
                    if (!is_dir($image_dir)) {
                        mkdir($image_dir, 0777, true);  // Tạo thư mục nếu chưa tồn tại
                    }

                    $image = $image_dir . time() . '.' . $image_ext;
                    if (!move_uploaded_file($image_tmp, $image)) {
                        $errors[] = "Không thể lưu ảnh. Vui lòng thử lại.";
                    }
                }
            }

            // Kiểm tra lỗi trước khi thêm vào cơ sở dữ liệu
            if (empty($errors)) {
                // Chuẩn bị truy vấn thêm ngành mới với PDO
                $stmt = $conn->prepare("INSERT INTO majors (name, description, img) VALUES (:major_name, :description, :img)");

                // Liên kết các tham số
                $stmt->bindParam(':major_name', $major_name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':img', $image, PDO::PARAM_STR);

                // Thực thi truy vấn
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Ngành đã được thêm thành công.";
    header("Location: major.php");
    exit();
                } else {
                    $errors[] = "Có lỗi khi thêm ngành mới. Vui lòng thử lại sau.";
                }

                $stmt->closeCursor();  // Đảm bảo đóng cursor sau khi thực thi truy vấn
            }
        }
    } catch (PDOException $e) {
        // Xử lý lỗi PDO
        $errors[] = "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
    }
}
?>
<style>
    /* Đảm bảo nút căn ngang hàng */
.form-actions {
    display: flex;
    justify-content: center; /* Căn 2 nút từ trái */
    gap: 10px; /* Khoảng cách giữa các nút */
    margin-top: 20px; /* Khoảng cách phía trên */
}

/* Tùy chỉnh kiểu dáng của nút */
.submit-button, .back-button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
}

/* Nút tạo */
.submit-button {
    background-color: #4CAF50; /* Màu xanh lá */
    color: white;
}

/* Nút quay lại */
.back-button {
    background-color: #f44336; /* Màu đỏ */
    color: white;
}

/* Thêm hiệu ứng hover cho nút */
.submit-button:hover {
    background-color: #45a049;
}

.back-button:hover {
    background-color: #e53935;
}

</style>
<!-- HTML form to create new major -->
<div class="content-wrapper">
    <div class="container">
        <div class="edit-major__center">
            <div class="edit-major">
                <h1>Tạo ngành mới</h1>

                <!-- Hiển thị lỗi hoặc thông báo thành công tại đây -->
                <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="color: green;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php elseif (!empty($errors)): ?>
                <div class="alert alert-danger" style="color: red;">
                    <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="major-name">Tên ngành</label>
                        <input type="text" id="major-name" name="major_name" placeholder="Nhập tên ngành" required />
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="4" placeholder="Nhập mô tả ngành"
                            required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="upload-image">Hình ảnh</label>
                        <div class="file-input">
                            <button type="button" class="upload-button" id="upload-image">Tải ảnh lên</button>
                            <input type="file" name="file_upload" id="file-upload" accept="image/*"
                                onchange="previewImage()" />
                        </div>
                        <div id="image-preview" style="margin-top: 10px;">
                            <!-- Ảnh xem trước sẽ được hiển thị ở đây -->
                        </div>
                    </div>
                    
                    <div class="form-actions">
        <button type="submit" class="submit-button" style = "width:50%;">Tạo</button>
        <button type="button" class="back-button" onclick="window.history.back()">Huỷ</button>
    </div>

                    
                    
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage() {
    const file = document.getElementById('file-upload').files[0];
    const preview = document.getElementById('image-preview');
    const reader = new FileReader();

    reader.onload = function(e) {
        const imageUrl = e.target.result;
        // Tạo một thẻ img mới để hiển thị ảnh
        preview.innerHTML = `<img src="${imageUrl}" alt="Ảnh xem trước" style="max-width: 100%; height: auto;" />`;
    };

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'footer.php'; ?>