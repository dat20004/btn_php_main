<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>
<script>
// Function to toggle content visibility
function showContent(section) {
    // Hide all content sections
    document.getElementById("personal-management").style.display =
        "none";
    document.getElementById("info").style.display = "none";
    document.getElementById("password").style.display = "none";
    document.getElementById("logout").style.display = "none";
    document.getElementById("account-management").style.display =
        "none";

    // Show selected section
    document.getElementById(section).style.display = "block";
}

// Function to toggle subcontent visibility within 'Quản Lý Cá Nhân'
function showSubContent(subSection) {
    // Hide all sub-content sections
    document.getElementById("student-exchange").style.display =
        "none";
    document.getElementById("documents").style.display = "none";
    document.getElementById("students").style.display = "none";
    document.getElementById("tests").style.display = "none";

    // Show selected sub-section
    document.getElementById(subSection).style.display = "block";
}

function toggleAccountMenu() {
    var submenu = document.getElementById('account-submenu');
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block'; // Hiển thị submenu
    } else {
        submenu.style.display = 'none'; // Ẩn submenu
    }
}


function togglePasswordVisibility(inputId, toggleIconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(toggleIconId);

    // Toggle the type attribute
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    // Toggle the icon
    toggleIcon.classList.toggle("fa-eye-slash");
    toggleIcon.classList.toggle("fa-eye");
}

document.getElementById("toggleCurrentPassword").addEventListener("click", function() {
    togglePasswordVisibility("currentPassword", "toggleCurrentPassword");
});

document.getElementById("toggleNewPassword").addEventListener("click", function() {
    togglePasswordVisibility("newPassword", "toggleNewPassword");
});

document.getElementById("toggleCheckPassword").addEventListener("click", function() {
    togglePasswordVisibility("checkPassword", "toggleCheckPassword");
}); // JavaScript để hiển thị hoặc ẩn danh sách bài học khi nhấn vào chương
function toggleLessons(chapterId) {
    var lessonList = document.getElementById('lessons_' + chapterId);

    // Kiểm tra trạng thái hiện tại và thay đổi display
    if (lessonList.style.display === "none" || lessonList.style.display === "") {
        lessonList.style.display = "block"; // Hiển thị danh sách bài học
    } else {
        lessonList.style.display = "none"; // Ẩn danh sách bài học khi nhấn lại
    }
}
</script>
</body>

</html>