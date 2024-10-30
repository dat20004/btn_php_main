<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Thống kê về khóa học
        </h1>

    </section>

    <!-- Main content -->

    <hr class="border border-3 bg-primary">
    <div class="statistics">
        <div class="statistics-list">
            <div class="statistics-item">
                <p>Số lượng học viên đã vào học trong thời gian chọn</p>
                <span>1</span>
                <p><i class="fa-solid fa-user"></i> Học viên</p>
            </div>
            <div class="statistics-item">
                <p>Số lượng học viên đã vào học trong thời gian chọn</p>
                <span>1</span>
                <p><i class="fa-solid fa-cart-shopping"></i> Tổng số học viên </p>
            </div>
            <div class="statistics-item">
                <p>Số lượng học viên đã vào học trong thời gian chọn</p>
                <span>1</span>
                <p><i class="fa-solid fa-tag"></i>Trung bình</p>
            </div>

        </div>

        <form action="" method="POST" role="form">
            <div class="form-group">
                <input type="text" placeholder="Nhập số điện thoại/Email">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Ngày học gần nhất</th>
                    <th>Số lần học</th>
                    <th>Mức độ hoàn thành</th>
                    <th>Ngày hoàn thành</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>

    </div>
</div>


<?php include 'footer.php' ?>