<?php
ob_start(); // Bắt đầu bộ đệm đầu ra
session_start();
include 'header.php';
include 'connect.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['userID'])) {
    echo "Vui lòng đăng nhập để xem chi tiết khóa học.";
    exit;
}

$student_id = $_SESSION['userID']; // Lấy ID của sinh viên từ session
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Kiểm tra nếu ID khóa học hợp lệ
if ($course_id > 0) {
    // Kiểm tra nếu người dùng đã đăng ký khóa học
    $sql_enrollment_check = "SELECT * FROM course_enrollments WHERE student_id = :student_id AND course_id = :course_id";
    $stmt_enrollment_check = $pdo->prepare($sql_enrollment_check);
    $stmt_enrollment_check->execute([':student_id' => $student_id, ':course_id' => $course_id]);
    $enrollment = $stmt_enrollment_check->fetch(PDO::FETCH_ASSOC);

    if (!$enrollment) {
        echo "Bạn chưa đăng ký khóa học này.";
        exit;
    }

    // Xử lý việc gửi câu hỏi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
        $question = trim($_POST['question']);
        
        if (!empty($question)) {
            // Chèn câu hỏi vào cơ sở dữ liệu
            $sql_insert_question = "INSERT INTO course_questions (course_id, student_id, question) VALUES (:course_id, :student_id, :question)";
            $stmt_insert_question = $pdo->prepare($sql_insert_question);
            $stmt_insert_question->execute([
                ':course_id' => $course_id,
                ':student_id' => $student_id,
                ':question' => $question,
            ]);

            // Chuyển hướng lại đến trang hiện tại
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $course_id);
            exit(); // Kết thúc script sau khi chuyển hướng
        }
    }

    // Lấy thông tin chi tiết khóa học
    $sql_course_details = "SELECT ch.id AS chapter_id, ch.title AS chapter_title, 
                           l.id AS lesson_id, l.title AS lesson_title
                           FROM chapters ch
                           LEFT JOIN lessons l ON ch.id = l.chapter_id
                           WHERE ch.course_id = :course_id
                           ORDER BY ch.id, l.id";

    try {
        $stmt_course_details = $pdo->prepare($sql_course_details);
        $stmt_course_details->execute([':course_id' => $course_id]);
        $course_details = $stmt_course_details->fetchAll(PDO::FETCH_ASSOC);

        if (empty($course_details)) {
            echo "Không tìm thấy nội dung cho khóa học này.";
        } else {
            // echo "<h2>Chi tiết khóa học</h2>";
            $last_chapter_id = null; // Để theo dõi chương cuối cùng đã hiển thị
        }
    } catch (PDOException $e) {
        echo "Lỗi khi truy vấn chi tiết khóa học: " . htmlspecialchars($e->getMessage());
    }

    // Lấy các câu hỏi và câu trả lời liên quan đến khóa học
    $sql_get_questions = "SELECT q.id AS question_id, q.question, u.full_name AS student_name, 
                          u.avatar AS avatar, q.create_at, a.answer, i.full_name AS instructor_name 
                          FROM course_questions q
                          JOIN users u ON q.student_id = u.id 
                          LEFT JOIN answers a ON q.id = a.question_id
                          LEFT JOIN users i ON a.replier_id  = i.id 
                          WHERE q.course_id = :course_id
                          ORDER BY q.create_at DESC";

    $stmt_get_questions = $pdo->prepare($sql_get_questions);
    $stmt_get_questions->execute([':course_id' => $course_id]);
    $questions = $stmt_get_questions->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Khóa học không hợp lệ.";
}
ob_end_flush(); 
?>

<style>
    .qa-frame {
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1000;
        top: 0;
        right: 0;
        background-color: #f1f1f1;
        overflow-x: hidden;
        transition: 0.5s;
        box-shadow: -2px 0 5px rgba(0,0,0,0.5);
    }
    .qa-frame.open {
        width: 400px;
    }
    .qa-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background-color: #007BFF;
        color: white;
    }
    .close-button {
        font-size: 30px;
        cursor: pointer;
    }
    .qa-content {
        padding: 20px;
        height: calc(100% - 60px); 
        overflow-y: auto;
    }
    .comment-section {
        display: flex;
        flex-direction: column;
    }
    .comment-input {
        margin-bottom: 20px;
    }
    .comment-input textarea {
        width: 100%;
        height: 100px;
        resize: none;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .btn-xuly {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-xuly .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .comment {
        display: flex;
        margin-bottom: 15px;
    }
    .comment img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
    }
    .comment-content {
        flex: 1;
    }
</style>

<!-- HTML để hiển thị chương và bài học -->
<section class="chitietbaihoc">
    <div class="container">
        <main class="main-content">
            <aside class="sidebar">
                <!-- Hiển thị chương và bài học -->
                <?php
                if (!empty($course_details)) {
                    foreach ($course_details as $row) {
                        // Kiểm tra và hiển thị chương mới
                        if ($last_chapter_id != $row['chapter_id']) {
                            if ($last_chapter_id !== null) {
                                echo "</ul>"; // Đóng danh sách bài học của chương trước
                            }
                            echo "<div class='chapter'>";
                            echo "<h2>" . htmlspecialchars($row['chapter_title']) . "</h2>";
                            echo "<ul class='lesson-list'>";
                            $last_chapter_id = $row['chapter_id'];
                        }

                        // Hiển thị bài học nếu có
                        if (!empty($row['lesson_title'])) {
                            echo "<li>";
                            echo "<a href='#' data-lesson='" . $row['lesson_id'] . "'>" . htmlspecialchars($row['lesson_title']) . "</a>";
                            echo "</li>";
                        }
                    }
                    echo "</ul></div>"; // Đóng danh sách bài học và chương cuối cùng
                } else {
                    echo "<p>Không có chương và bài học nào.</p>";
                }
                ?>
            </aside>
            <section class="content">
                <div class="section" id="lesson-content">
                    <p>Nội dung của bài học đã chọn sẽ xuất hiện ở đây.</p>
                </div>
                <div class="buttons">
                    <button class="previous">Bài trước</button>
                    <button class="next">Bài kế tiếp</button>
                </div>

                <!-- Nút mở khung Hỏi Đáp -->
                <button id="openFrame" class="btn" style="margin-left:710px;margin-top:500px;">Hỏi đáp</button>
            </section>
        </main>

        <!-- Khung Hỏi Đáp -->
        <div id="qaFrame" class="qa-frame">
            <div class="qa-header">
                <h2>Hỏi Đáp</h2>
                <span class="close-button" id="closeFrame">&times;</span>
            </div>
            <div class="qa-content">
                <div class="comment-section">
                    <div class="comment-input">
                        <form id="questionForm" method="POST" action="">
                            <textarea name="question" placeholder="Nhập câu hỏi của bạn" required></textarea>
                            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                            <div class="btn-xuly">
                                <button class="btn" type="submit">Gửi</button>
                                <button class="btn" type="reset">Hủy</button>
                            </div>
                        </form>
                    </div>

                    <!-- Hiển thị các câu hỏi hiện có -->
                    <p><strong><?php echo count($questions); ?> bình luận</strong></p>
                    <?php 
                    // Đường dẫn đến hình ảnh mặc định
                    $default_avatar = './images/avatarmd.png'; // Thay đổi đường dẫn đến ảnh mặc định của bạn

                    foreach ($questions as $question): 
                    ?>
                    <div class="comment">
                        <?php 
                        // Kiểm tra và hiển thị ảnh đại diện, nếu không có thì dùng ảnh mặc định
                        $avatar = !empty($question["avatar"]) ?  htmlspecialchars($question["avatar"]) : $default_avatar; 
                        echo '<img src="' . $avatar . '" alt="Avatar">';
                        ?>
                        <div class="comment-content">
                            <p><strong><?php echo htmlspecialchars($question['student_name']); ?></strong> <em><?php echo htmlspecialchars($question['create_at']); ?></em></p>
                            <p><?php echo htmlspecialchars($question['question']); ?></p>
                            <?php if (!empty($question['answer'])): ?>
                                <div class="answer">
                                    <strong>Giảng viên: <?php echo htmlspecialchars($question['instructor_name']); ?></strong>
                                    <div class="answer-text"><?php echo htmlspecialchars($question['answer']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:center;"><a href="form_feedback.php?course_id=<?php echo htmlspecialchars($course_id); ?>">Đánh giá</a></div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const lessonLinks = document.querySelectorAll('.lesson-list a');
    const openButton = document.getElementById('openFrame');
    const closeButton = document.getElementById('closeFrame');
    const qaFrame = document.getElementById('qaFrame');

    lessonLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const lessonId = this.getAttribute('data-lesson');
            loadLessonContent(lessonId);
        });
    });

    openButton.addEventListener('click', () => {
        qaFrame.classList.add('open');
    });

    closeButton.addEventListener('click', () => {
        qaFrame.classList.remove('open');
    });

    // Hàm để tải nội dung bài học
    function loadLessonContent(lessonId) {
        fetch(`getLessonContent.php?lesson_id=${lessonId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('lesson-content').innerHTML = data;
            })
            .catch(error => {
                console.error('Lỗi:', error);
                document.getElementById('lesson-content').innerHTML = '<p>Không thể tải nội dung bài học.</p>';
            });
    }
});
    document.getElementById('openFrame').onclick = function() {
        document.getElementById('qaFrame').classList.add('open');
    }
    document.getElementById('closeFrame').onclick = function() {
        document.getElementById('qaFrame').classList.remove('open');
    }
</script>

<?php
ob_end_flush(); 
include 'footer.php'; // Kết thúc bộ đệm và gửi ra
?>
