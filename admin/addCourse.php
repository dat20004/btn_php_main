<?php
session_start(); 
include 'header.php';
include '../connect.php';

// Bắt đầu session để lưu thông báo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $courseName = $_POST['courseName'];
    $courseDesc = $_POST['courseDesc'];
    $courseField = $_POST['courseField'];
    $courseTeacher = $_POST['courseTeacher'];
    $coursePrice = $_POST['coursePrice'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    
    // Xử lý file upload (hình ảnh khóa học)
    $image = null;
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) { // Đảm bảo tên input đúng
        $image_name = $_FILES['file_upload']['name'];
        $image_tmp = $_FILES['file_upload']['tmp_name'];
        $image_size = $_FILES['file_upload']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Allowed extensions
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($image_ext), $allowed_exts)) {
            $_SESSION['error'] = "Ảnh phải có định dạng: jpg, jpeg, png, gif.";
            header("Location: addCourse.php");
            exit();
        }

        if ($image_size > 100 * 1024 * 1024) {  // Giới hạn ảnh dưới 100MB
            $_SESSION['error'] = "Ảnh không được vượt quá 100MB.";
            header("Location: addCourse.php");
            exit();
        }

        // If valid, move the uploaded image to the target directory
        $image_dir = 'uploads/';
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0777, true);
        }

        $image = $image_dir . time() . '.' . $image_ext;
        if (!move_uploaded_file($image_tmp, $image)) {
            $_SESSION['error'] = "Không thể lưu ảnh. Vui lòng thử lại.";
            header("Location: addCourse.php");
            exit();
        }
    }

    // Kiểm tra trùng tên khóa học
    try {
        $sqlCheck = "SELECT COUNT(*) FROM courses WHERE name = :name";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindParam(':name', $courseName);
        $stmtCheck->execute();
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            // Nếu khóa học đã tồn tại, hiển thị thông báo lỗi
            $_SESSION['error'] = "Tên khóa học đã tồn tại.";
            header("Location: addCourse.php");
            exit();
        } else {
            // Nếu không trùng, thêm khóa học vào cơ sở dữ liệu
            // Nếu không trùng, thêm khóa học vào cơ sở dữ liệu
$sql = "INSERT INTO courses (name, description, major_id, teacher_id, fee, start_date, end_date, image) 
VALUES (:name, :description, :major_id, :teacher_id, :fee, :start_date, :end_date, :image)";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':name', $courseName);
$stmt->bindParam(':description', $courseDesc);
$stmt->bindParam(':major_id', $courseField);
$stmt->bindParam(':teacher_id', $courseTeacher);
$stmt->bindParam(':fee', $coursePrice);
$stmt->bindParam(':start_date', $startDate);
$stmt->bindParam(':end_date', $endDate);
$stmt->bindParam(':image', $image); // Sửa thành $image

// Thực thi câu lệnh
try {
$stmt->execute();
// Hiển thị thông báo thành công và chuyển hướng về trang danh sách khóa học
$_SESSION['success'] = "Khóa học đã được thêm thành công.";
header("Location: course.php");
exit();
} catch (PDOException $e) {
$_SESSION['error'] = "Lỗi khi thêm khóa học: " . $e->getMessage();
header("Location: addCourse.php");
exit();
}

        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi khi thêm khóa học: " . $e->getMessage();
        header("Location: addCourse.php");
        exit();
    }
}

// Lấy danh sách ngành từ bảng majors
try {
    $sql = "SELECT id, name FROM majors";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($fields)) {
        echo "Không có dữ liệu ngành.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách ngành: " . $e->getMessage();
}

// Lấy danh sách giảng viên từ bảng users với role là 'Teacher'
try {
    $sqlTeachers = "SELECT id, full_name FROM users WHERE role = 'Teacher'";
    $stmtTeachers = $pdo->prepare($sqlTeachers);
    $stmtTeachers->execute();
    $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);
    if (empty($teachers)) {
        echo "Không có giảng viên nào.";
    }
} catch (PDOException $e) {
    echo "Lỗi khi lấy danh sách giảng viên: " . $e->getMessage();
}

$pdo = null;
?>

<style>
/* Đảm bảo nút căn ngang hàng */
.form-actions {
    display: flex;
    justify-content: center;
    /* Căn 2 nút từ trái */
    gap: 10px;
    /* Khoảng cách giữa các nút */
    margin-top: 20px;
    /* Khoảng cách phía trên */
}

/* Tùy chỉnh kiểu dáng của nút */
.submit-button,
.back-button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
}

/* Nút tạo */
.submit-button {
    background-color: #4CAF50;
    /* Màu xanh lá */
    color: white;
}

/* Nút quay lại */
.back-button {
    background-color: #f44336;
    /* Màu đỏ */
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

<div class="content-wrapper">
    <section class="courseForm-container">
        <div id="courseForm" style="display: block;" class="courseForm">
            <h1>Thêm khóa học</h1>

            <!-- Hiển thị thông báo nếu có -->
            <?php
            // Hiển thị thông báo lỗi nếu có
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
            }
            // Hiển thị thông báo thành công nếu có
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
            }
            ?>

            <form id="addCourseForm" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="courseName">Tên khóa học:</label><br>
                    <input type="text" id="courseName" name="courseName" placeholder="Nhập tên khóa học" required>
                </div>

                <div>
                    <label for="courseDesc">Mô tả khóa học:</label><br>
                    <textarea id="courseDesc" name="courseDesc" placeholder="Nhập mô tả" required></textarea>
                </div>

                <div>
                    <label for="courseField">Ngành:</label><br>
                    <select name="courseField" id="courseField" required>
                        <option value="">Chọn ngành</option>
                        <?php
                        foreach ($fields as $field) {
                            echo "<option value='" . $field['id'] . "'>" . $field['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="courseTeacher">Giảng viên:</label><br>
                    <select name="courseTeacher" id="courseTeacher" required>
                        <option value="">Chọn giảng viên</option>
                        <?php
                        foreach ($teachers as $teacher) {
                            echo "<option value='" . $teacher['id'] . "'>" . $teacher['full_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="coursePrice">Giá:</label><br>
                    <input type="text" id="coursePrice" name="coursePrice" placeholder="Nhập giá khóa học" required>
                </div>

                <div>
                    <label for="startDate">Ngày bắt đầu:</label><br>
                    <input type="date" id="startDate" name="startDate" required>
                </div>

                <div>
                    <label for="endDate">Ngày kết thúc:</label><br>
                    <input type="date" id="endDate" name="endDate" required>
                </div>

                <div class="course__Img">
                    <label for="courseImage">Hình ảnh</label>
                    <div class="upload-box" onclick="document.getElementById('courseImage').click()">
                        <span>Tải ảnh lên</span>
                        <input type="file" id="courseImage" name="file_upload" accept="image/*"
                            onchange="previewImage()" style="display:none;">
                    </div>
                    <div id="image-preview" style="margin-top: 10px;">
                        <!-- Ảnh xem trước sẽ được hiển thị ở đây -->
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-button">Lưu</button>
                    <button type="button" class="back-button" onclick="window.history.back()">Huỷ</button>
                </div>

            </form>
        </div>
    </section>
</div>

<script>
function previewImage() {
    const file = document.getElementById('courseImage').files[0]; // Lấy file từ ô nhập
    const preview = document.getElementById('image-preview'); // Đối tượng để hiển thị ảnh xem trước
    const reader = new FileReader(); // Tạo FileReader để đọc tệp

    reader.onload = function(e) {
        const imageUrl = e.target.result; // Lấy URL của ảnh đã được đọc
        // Tạo một thẻ img mới để hiển thị ảnh
        preview.innerHTML = `<img src="${imageUrl}" alt="Ảnh xem trước" style="max-width: 100%; height: auto;" />`;
    };

    if (file) {
        reader.readAsDataURL(file); // Đọc tệp dưới dạng URL
    }
}
</script>

<?php include 'footer.php'; ?>